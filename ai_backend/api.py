import sys, os, pathlib
import torch, cv2, numpy as np, base64
from flask import Flask, request, jsonify, Response
from flask_cors import CORS

# Setup path YOLOv5
current_dir = os.path.dirname(os.path.abspath(__file__))
yolov5_path = os.path.join(current_dir, '..', 'yolov5')
sys.path.append(yolov5_path)

# Patch PosixPath untuk Windows
temp = pathlib.PosixPath
pathlib.PosixPath = pathlib.WindowsPath

# YOLOv5 dependencies
from models.common import DetectMultiBackend
from utils.general import non_max_suppression, scale_boxes
from utils.torch_utils import select_device

# Flask app
app = Flask(__name__)
CORS(app)  # Enable CORS for all routes

# Configuration parameters
DEBUG_MODE = False           # Set ke False untuk produksi

# Threshold konfigurasi untuk deteksi
CONF_THRESHOLD = 0.6
IOU_THRESHOLD = 0.5 # Threshold IOU untuk mengurangi penghapusan deteksi yang berdekatan

# Model configuration
MODEL_PATH = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'model', 'yolov5m.pt')

# Egg-related classes - diperluas untuk menangkap variasi label
EGG_CLASSES = ['Mutu 1', 'Mutu 2', 'Mutu 3']

# Load model
device = select_device('')  # '' will automatically select the best available device (CUDA/CPU)
model = DetectMultiBackend(MODEL_PATH, device=device)
model.eval()

def preprocess_image(img):
    """
    Enhanced preprocessing untuk meningkatkan deteksi
    """
    try:
        # Konversi ke LAB color space untuk normalisasi pencahayaan yang lebih baik
        lab = cv2.cvtColor(img, cv2.COLOR_RGB2LAB)
        
        # Pisahkan channel L (Lightness)
        l, a, b = cv2.split(lab)
        
        # Terapkan Histogram Equalization pada channel L
        l = cv2.equalizeHist(l)
        
        # Gabungkan kembali channel
        enhanced_lab = cv2.merge([l, a, b])
        
        # Konversi kembali ke RGB
        enhanced_img = cv2.cvtColor(enhanced_lab, cv2.COLOR_LAB2RGB)
        
        # Sharpening - meningkatkan deteksi tepi pada cangkang telur
        kernel_sharpening = np.array([[-1,-1,-1], 
                                      [-1, 9,-1],
                                      [-1,-1,-1]])
        enhanced_img = cv2.filter2D(enhanced_img, -1, kernel_sharpening)
        
        # Noise reduction dengan Bilateral Filter
        enhanced_img = cv2.bilateralFilter(enhanced_img, 9, 75, 75)
        
        # Normalize color channels
        enhanced_img = cv2.normalize(enhanced_img, None, 0, 255, cv2.NORM_MINMAX)
        
        # Slight contrast boost untuk deteksi lebih baik
        enhanced_img = cv2.convertScaleAbs(enhanced_img, alpha=1.1, beta=0)
        
        return enhanced_img
    except Exception as e:
        print(f"Advanced preprocessing error: {e}, falling back to simple preprocessing")
        try:
            # Simple preprocessing as fallback
            lab = cv2.cvtColor(img, cv2.COLOR_RGB2LAB)
            l, a, b = cv2.split(lab)
            l = cv2.equalizeHist(l)
            enhanced_lab = cv2.merge([l, a, b])
            enhanced_img = cv2.cvtColor(enhanced_lab, cv2.COLOR_LAB2RGB)
            enhanced_img = cv2.GaussianBlur(enhanced_img, (3, 3), 0)
            return enhanced_img
        except Exception as e2:
            print(f"Simple preprocessing error: {e2}, using original image")
            return img

def simple_detect(img, model, device, conf_threshold, iou_threshold):
    """
    Simple detection function
    """
    try:
        img_resized = cv2.resize(img, (640, 640))
        img_tensor = torch.from_numpy(img_resized).permute(2, 0, 1).unsqueeze(0).float() / 255.0
        img_tensor = img_tensor.to(device)
        
        with torch.no_grad():
            pred = model(img_tensor)
            pred = non_max_suppression(pred, conf_thres=conf_threshold, iou_thres=iou_threshold)
            
            for det in pred:
                if len(det):
                    det[:, :4] = scale_boxes(img_tensor.shape[2:], det[:, :4], img.shape).round()
        
        return pred
    except Exception as e:
        print(f"Simple detection error: {e}")
        return [[]]

@app.route('/debug')
def debug():
    # Cek packages yang terinstal
    import sys, pkg_resources
    
    installed_packages = [f"{pkg.key} {pkg.version}" for pkg in pkg_resources.working_set 
                         if pkg.key in ['numpy', 'scipy', 'torch', 'opencv-python']]
    
    # Debug model classes
    model_classes = {}
    try:
        if hasattr(model, 'names'):
            model_classes = dict(model.names)
        elif hasattr(model, 'model') and hasattr(model.model, 'names'):
            model_classes = dict(model.model.names)
    except Exception as e:
        model_classes = {"error": str(e)}
    
    return jsonify({
        'python_version': sys.version,
        'python_path': sys.executable,
        'installed_packages': installed_packages,
        'config': {
            'conf_threshold': CONF_THRESHOLD,
            'iou_threshold': IOU_THRESHOLD,
            'debug_mode': DEBUG_MODE
        },
        'model_classes': model_classes,
        'expected_egg_classes': EGG_CLASSES
    })

@app.route('/')
def index():
    return "API Flask YOLOv5 aktif!"

@app.route('/status')
def status():
    detection_mode = "simple"  # Always simple detection now
    
    # Get model name
    model_name = os.path.basename(MODEL_PATH)
    
    return jsonify({
        'status': 'online',
        'device': str(device),
        'model': model_name,
        'egg_classes': EGG_CLASSES,
        'threshold': CONF_THRESHOLD,
        'iou_threshold': IOU_THRESHOLD,
        'preprocessing': {
            'enabled': True,
            'features': ['Histogram Equalization', 'Sharpening', 'Bilateral Filter', 'Color Normalization']
        },
        'detection_mode': detection_mode
    })

@app.route('/set_threshold', methods=['POST'])
def set_threshold():
    """
    Endpoint untuk menyesuaikan threshold deteksi secara dinamis
    """
    global CONF_THRESHOLD, IOU_THRESHOLD
    
    data = request.json
    if not data:
        return jsonify({'error': 'No data provided'}), 400
    
    if 'conf_threshold' in data:
        new_conf = float(data['conf_threshold'])
        if 0.1 <= new_conf <= 0.9:
            CONF_THRESHOLD = new_conf
        else:
            return jsonify({'error': 'Confidence threshold must be between 0.1 and 0.9'}), 400
    
    if 'iou_threshold' in data:
        new_iou = float(data['iou_threshold'])
        if 0.1 <= new_iou <= 0.9:
            IOU_THRESHOLD = new_iou
        else:
            return jsonify({'error': 'IOU threshold must be between 0.1 and 0.9'}), 400
    
    return jsonify({
        'message': 'Configuration updated successfully',
        'conf_threshold': CONF_THRESHOLD,
        'iou_threshold': IOU_THRESHOLD,
        'detection_mode': 'simple'
    })

@app.route('/detect', methods=['POST'])
def detect():
    try:
        if 'image' not in request.files:
            return jsonify({'error': 'No image uploaded'}), 400

        file = request.files['image']
        img_bytes = np.frombuffer(file.read(), np.uint8)
        img = cv2.imdecode(img_bytes, cv2.IMREAD_COLOR)
        
        if img is None:
            return jsonify({'error': 'Invalid image format'}), 400
            
        img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
        
        # Apply preprocessing untuk meningkatkan kualitas gambar
        try:
            img_enhanced = preprocess_image(img)
        except Exception as e:
            print(f"Preprocessing failed: {e}, using original image")
            img_enhanced = img
        
        # Use simple detection
        print("Using simple detection...")
        pred = simple_detect(img_enhanced, model, device, CONF_THRESHOLD, IOU_THRESHOLD)

        results = []
        egg_counts = {'Mutu 1': 0, 'Mutu 2': 0, 'Mutu 3': 0}
        drawn_boxes = []  # Track untuk menghindari duplikasi gambar bounding box
        raw_detection_count = 0
        
        for det in pred:
            if len(det):
                raw_detection_count += len(det)
                for *xyxy, conf, cls in det:
                    try:
                        confidence = float(conf)
                        class_id = int(cls)
                        label = model.names[class_id]
                        
                        # Debug: Print actual label from model
                        print(f"Debug - Detected label: '{label}', Class ID: {class_id}, Confidence: {confidence:.3f}")
                        
                        # Check if the detected object is an egg (dengan pengecekan yang lebih fleksibel)
                        is_egg_class = any(egg_class.lower() in label.lower() or label.lower() in egg_class.lower() 
                                  for egg_class in EGG_CLASSES)
                        
                        print(f"Debug - Is egg class: {is_egg_class}, EGG_CLASSES: {EGG_CLASSES}")
                        
                        if confidence >= CONF_THRESHOLD and is_egg_class:
                            x1, y1, x2, y2 = map(int, xyxy)
                            
                            # Validasi koordinat
                            x1, y1 = max(0, x1), max(0, y1)
                            x2, y2 = min(img.shape[1], x2), min(img.shape[0], y2)
                            
                            if x2 <= x1 or y2 <= y1:
                                continue  # Skip invalid boxes
                            
                            # Cek duplikasi berdasarkan area overlap
                            box_area = (x2 - x1) * (y2 - y1)
                            is_duplicate = False
                            
                            for existing_box in drawn_boxes:
                                ex1, ey1, ex2, ey2 = existing_box
                                # Hitung intersection
                                ix1, iy1 = max(x1, ex1), max(y1, ey1)
                                ix2, iy2 = min(x2, ex2), min(y2, ey2)
                                
                                if ix1 < ix2 and iy1 < iy2:
                                    intersection = (ix2 - ix1) * (iy2 - iy1)
                                    overlap_ratio = intersection / box_area if box_area > 0 else 0
                                    if overlap_ratio > 0.5:  # 50% overlap dianggap duplikasi
                                        is_duplicate = True
                                        break
                            
                            if not is_duplicate:
                                drawn_boxes.append([x1, y1, x2, y2])
                                
                                # Add to results
                                results.append({
                                    'label': label,
                                    'confidence': confidence,
                                    'bbox': [x1, y1, x2, y2]
                                })
                                
                                # Count eggs by quality
                                if 'mutu 1' in label.lower() or 'mutu1' in label.lower():
                                    egg_counts['Mutu 1'] += 1
                                    color = (0, 255, 0)  
                                elif 'mutu 2' in label.lower() or 'mutu2' in label.lower():
                                    egg_counts['Mutu 2'] += 1
                                    color = (255, 255, 0) 
                                elif 'mutu 3' in label.lower() or 'mutu3' in label.lower():
                                    egg_counts['Mutu 3'] += 1
                                    color = (255, 0, 0)  
                                else:
                                    # Default color for any other egg detections
                                    color = (0, 255, 255)

                                # Draw bounding box dengan ketebalan yang lebih tebal
                                cv2.rectangle(img, (x1, y1), (x2, y2), color, 3)

                                # Draw label + confidence with background
                                text = f'{label} {confidence:.2f}'
                                (tw, th), baseline = cv2.getTextSize(text, cv2.FONT_HERSHEY_SIMPLEX, 0.7, 2)
                                cv2.rectangle(img, (x1, y1 - th - 12), (x1 + tw, y1), color, -1)
                                cv2.putText(img, text, (x1, y1 - 6), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 0, 0), 2)
                    except Exception as e:
                        print(f"Error processing detection: {e}")
                        continue

        # Encode hasil deteksi ke base64
        img_rgb = cv2.cvtColor(img, cv2.COLOR_RGB2BGR)
        _, buffer = cv2.imencode('.jpg', img_rgb, [cv2.IMWRITE_JPEG_QUALITY, 95])
        img_base64 = base64.b64encode(buffer).decode('utf-8')

        result = {
            'detections': results,
            'image_base64': img_base64,
            'egg_counts': egg_counts,
            'total_eggs': sum(egg_counts.values()),
            'detection_info': {
                'confidence_threshold': CONF_THRESHOLD,
                'total_raw_detections': raw_detection_count,
                'filtered_detections': len(results)
            }
        }
            
        return jsonify(result)
        
    except Exception as e:
        print(f"Detection error: {str(e)}")
        return jsonify({
            'error': f'Detection failed: {str(e)}',
            'detections': [],
            'image_base64': '',
            'egg_counts': {'Mutu 1': 0, 'Mutu 2': 0, 'Mutu 3': 0},
            'total_eggs': 0
        }), 500

@app.route('/video_feed')
def video_feed():
    def gen_frames():
        cap = cv2.VideoCapture(0)
        while True:
            success, frame = cap.read()
            if not success:
                break

            img = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
            img_resized = cv2.resize(img, (640, 640))
            img_tensor = torch.from_numpy(img_resized).permute(2, 0, 1).unsqueeze(0).float() / 255.0
            img_tensor = img_tensor.to(device)

            with torch.no_grad():
                pred = model(img_tensor)
                pred = non_max_suppression(pred, conf_thres=CONF_THRESHOLD, iou_thres=IOU_THRESHOLD)

            for det in pred:
                if len(det):
                    det[:, :4] = scale_boxes(img_tensor.shape[2:], det[:, :4], frame.shape).round()
                    for *xyxy, conf, cls in det:
                        confidence = float(conf)
                        class_id = int(cls)
                        label = model.names[class_id]
                        
                        # Only process eggs
                        is_egg_class = any(egg_class.lower() in label.lower() or label.lower() in egg_class.lower() 
                               for egg_class in EGG_CLASSES)
                        
                        if confidence >= CONF_THRESHOLD and is_egg_class:
                            x1, y1, x2, y2 = map(int, xyxy)
                            
                            # Set color based on egg quality
                            if 'mutu 1' in label.lower():
                                color = (0, 255, 0)  # Green for Mutu 1
                            elif 'mutu 2' in label.lower():
                                color = (255, 255, 0)  # Yellow for Mutu 2
                            elif 'mutu 3' in label.lower():
                                color = (0, 0, 255)  # Red for Mutu 3
                            else:
                                color = (0, 255, 255)  # Cyan for other egg types
                                
                            cv2.rectangle(frame, (x1, y1), (x2, y2), color, 2)
                            cv2.putText(frame, f'{label} {confidence:.2f}', (x1, y1 - 10),
                                       cv2.FONT_HERSHEY_SIMPLEX, 0.5, color, 2)

            ret, buffer = cv2.imencode('.jpg', frame)
            frame = buffer.tobytes()
            yield (b'--frame\r\n'
                   b'Content-Type: image/jpeg\r\n\r\n' + frame + b'\r\n')
        
        cap.release()

    return Response(gen_frames(),
                    mimetype='multipart/x-mixed-replace; boundary=frame')

@app.route('/webcam_detect', methods=['POST'])
def webcam_detect():
    # Get base64 image from request
    data = request.json
    if 'image' not in data:
        return jsonify({'error': 'No image data received'}), 400
    
    try:
        # Extract base64 image
        image_data = data['image'].split(',')[1] if ',' in data['image'] else data['image']
        image_bytes = base64.b64decode(image_data)
        
        # Convert to cv2 image
        nparr = np.frombuffer(image_bytes, np.uint8)
        img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
        
        if img is None:
            return jsonify({'error': 'Invalid image format'}), 400
            
        img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
        
        # Prepare for model
        img_resized = cv2.resize(img, (640, 640))
        img_tensor = torch.from_numpy(img_resized).permute(2, 0, 1).unsqueeze(0).float() / 255.0
        img_tensor = img_tensor.to(device)
        
        # Detection
        with torch.no_grad():
            pred = model(img_tensor)
            pred = non_max_suppression(pred, conf_thres=CONF_THRESHOLD, iou_thres=IOU_THRESHOLD)
        
        results = []
        egg_counts = {'Mutu 1': 0, 'Mutu 2': 0, 'Mutu 3': 0}
        
        for det in pred:
            if len(det):
                det[:, :4] = scale_boxes(img_tensor.shape[2:], det[:, :4], img.shape).round()
                for *xyxy, conf, cls in det:
                    confidence = float(conf)
                    class_id = int(cls)
                    label = model.names[class_id]
                    
                    # Filter for eggs only
                    is_egg_class = any(egg_class.lower() in label.lower() or label.lower() in egg_class.lower() 
                           for egg_class in EGG_CLASSES)
                    
                    if confidence >= CONF_THRESHOLD and is_egg_class:
                        x1, y1, x2, y2 = map(int, xyxy)
                        
                        # Add to results
                        results.append({
                            'label': label,
                            'confidence': confidence,
                            'bbox': [x1, y1, x2, y2]
                        })
                        
                        # Count eggs by quality
                        if 'mutu 1' in label.lower():
                            egg_counts['Mutu 1'] += 1
                            color = (0, 255, 0)  # Green
                        elif 'mutu 2' in label.lower():
                            egg_counts['Mutu 2'] += 1
                            color = (255, 255, 0)  # Yellow
                        elif 'mutu 3' in label.lower():
                            egg_counts['Mutu 3'] += 1
                            color = (0, 0, 255)  # Red
                        else:
                            color = (0, 255, 255)  # Cyan
                            
                        # Draw bounding box
                        cv2.rectangle(img, (x1, y1), (x2, y2), color, 2)
                        
                        # Draw label
                        text = f'{label} {confidence:.2f}'
                        (tw, th), baseline = cv2.getTextSize(text, cv2.FONT_HERSHEY_SIMPLEX, 0.6, 2)
                        cv2.rectangle(img, (x1, y1 - th - 10), (x1 + tw, y1), color, -1)
                        cv2.putText(img, text, (x1, y1 - 5), cv2.FONT_HERSHEY_SIMPLEX, 0.6, (0, 0, 0), 2)
        
        # Encode processed image
        img_rgb = cv2.cvtColor(img, cv2.COLOR_RGB2BGR)
        _, buffer = cv2.imencode('.jpg', img_rgb)
        img_base64 = base64.b64encode(buffer).decode('utf-8')
        
        # Return results
        result = {
            'detections': results,
            'image_base64': img_base64,
            'egg_counts': egg_counts,
            'total_eggs': sum(egg_counts.values())
        }
            
        return jsonify(result)
        
    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    print("=" * 50)
    print("🥚 SIMPLE EGG DETECTION API")
    print("=" * 50)
    
    # Model info
    model_name = os.path.basename(MODEL_PATH)
    print(f"📁 Model: {model_name}")
    print(f"💻 Device: {device}")
    print("-" * 50)
    
    print("🔧 CONFIGURATION:")
    print(f"   • Detection Mode: Simple")
    print(f"   • Confidence Threshold: {CONF_THRESHOLD}")
    print(f"   • IOU Threshold: {IOU_THRESHOLD}")
    print(f"   • Input Size: 640x640")
    
    print(f"\n🥚 Egg Classes: {EGG_CLASSES}")
    print("-" * 50)
    print("📡 ENDPOINTS:")
    print("   • POST /detect - Image detection")
    print("   • POST /webcam_detect - Webcam detection")
    print("   • GET /video_feed - Live video stream")
    print("   • POST /set_threshold - Adjust thresholds")
    print("   • GET /status - System status")
    print("-" * 50)
    
    app.run(debug=DEBUG_MODE, port=5000)
