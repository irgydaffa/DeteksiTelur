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
DEBUG_MODE = True           # Set ke False untuk produksi

# Threshold konfigurasi
CONF_THRESHOLD = 0.5  # Confidence minimal
IOU_THRESHOLD = 0.45  # IOU threshold untuk NMS

# Egg-related classes
EGG_CLASSES = ['Mutu 1', 'Mutu 2', 'Mutu 3']

# Load model
device = select_device('')
MODEL_PATH = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'model', 'yolov5s.pt')
model = DetectMultiBackend(MODEL_PATH, device=device)
model.eval()

@app.route('/debug')
def debug():
    # Cek packages yang terinstal
    import sys, pkg_resources
    
    installed_packages = [f"{pkg.key} {pkg.version}" for pkg in pkg_resources.working_set 
                         if pkg.key in ['numpy', 'scipy', 'torch', 'opencv-python']]
    
    return jsonify({
        'python_version': sys.version,
        'python_path': sys.executable,
        'installed_packages': installed_packages,
        'config': {
            'conf_threshold': CONF_THRESHOLD,
            'iou_threshold': IOU_THRESHOLD,
            'debug_mode': DEBUG_MODE
        }
    })

@app.route('/')
def index():
    return "API Flask YOLOv5 aktif!"

@app.route('/status')
def status():
    return jsonify({
        'status': 'online',
        'device': str(device),
        'model_path': MODEL_PATH,
        'egg_classes': EGG_CLASSES,
        'threshold': CONF_THRESHOLD,
        'preprocessing': False
    })

@app.route('/detect', methods=['POST'])
def detect():
    if 'image' not in request.files:
        return jsonify({'error': 'No image uploaded'}), 400

    file = request.files['image']
    img_bytes = np.frombuffer(file.read(), np.uint8)
    img = cv2.imdecode(img_bytes, cv2.IMREAD_COLOR)
    img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)

    img_resized = cv2.resize(img, (640, 640))
    img_tensor = torch.from_numpy(img_resized).permute(2, 0, 1).unsqueeze(0).float() / 255.0
    img_tensor = img_tensor.to(device)

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
                
                # Check if the detected object is an egg (based on class name)
                is_egg_class = any(egg_class.lower() in label.lower() or label.lower() in egg_class.lower() 
                          for egg_class in EGG_CLASSES)
                
                if confidence >= CONF_THRESHOLD and is_egg_class:  # Only process if it's an egg class
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
                        color = (0, 255, 0)  # Green for Mutu 1
                    elif 'mutu 2' in label.lower():
                        egg_counts['Mutu 2'] += 1
                        color = (255, 255, 0)  # Yellow for Mutu 2
                    elif 'mutu 3' in label.lower():
                        egg_counts['Mutu 3'] += 1
                        color = (255, 255, 255)  # Red for Mutu 3
                    else:
                        # Default color for any other egg detections
                        color = (0, 255, 255)

                    # Draw bounding box
                    cv2.rectangle(img, (x1, y1), (x2, y2), color, 2)

                    # Draw label + confidence with background
                    text = f'{label} {confidence:.2f}'
                    (tw, th), baseline = cv2.getTextSize(text, cv2.FONT_HERSHEY_SIMPLEX, 0.6, 2)
                    cv2.rectangle(img, (x1, y1 - th - 10), (x1 + tw, y1), color, -1)
                    cv2.putText(img, text, (x1, y1 - 5), cv2.FONT_HERSHEY_SIMPLEX, 0.6, (0, 0, 0), 2)

    # Encode hasil deteksi ke base64
    img_rgb = cv2.cvtColor(img, cv2.COLOR_RGB2BGR)
    _, buffer = cv2.imencode('.jpg', img_rgb)
    img_base64 = base64.b64encode(buffer).decode('utf-8')

    result = {
        'detections': results,
        'image_base64': img_base64,
        'egg_counts': egg_counts,
        'total_eggs': sum(egg_counts.values())
    }
        
    return jsonify(result)

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
                            color = (0, 0, )  # Red
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
    print("-" * 40)
    print(f"Model loaded: {MODEL_PATH}")
    print(f"YOLO Path: {yolov5_path}")
    print(f"Device: {device}")
    print("-" * 40)
    app.run(debug=True, port=5000)