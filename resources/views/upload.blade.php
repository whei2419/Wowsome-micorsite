<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Upload Lantern Image</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .upload-area {
            border: 3px dashed #667eea;
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8f9ff;
            margin-bottom: 20px;
        }

        .upload-area:hover {
            border-color: #764ba2;
            background: #f0f1ff;
        }

        .upload-area.dragging {
            border-color: #764ba2;
            background: #e8e9ff;
            transform: scale(1.02);
        }

        .upload-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }

        .upload-text {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .upload-hint {
            color: #999;
            font-size: 12px;
        }

        #fileInput {
            display: none;
        }

        .preview-container {
            margin: 20px 0;
            text-align: center;
            display: none;
        }

        .preview-container.active {
            display: block;
        }

        .preview-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
        }

        .file-info {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-upload {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-upload:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-upload:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-clear {
            background: #f1f1f1;
            color: #666;
            margin-top: 10px;
        }

        .btn-clear:hover {
            background: #e1e1e1;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background: #f1f1f1;
            border-radius: 10px;
            overflow: hidden;
            margin: 20px 0;
            display: none;
        }

        .progress-bar.active {
            display: block;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            width: 0%;
            transition: width 0.3s ease;
        }

        .message {
            padding: 12px;
            border-radius: 8px;
            margin: 15px 0;
            display: none;
            font-size: 14px;
        }

        .message.active {
            display: block;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .result-links {
            margin-top: 15px;
        }

        .result-links a {
            display: inline-block;
            padding: 8px 16px;
            margin: 5px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .result-links a:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🏮 Upload Lantern</h1>
        <p class="subtitle">Share your wishing lantern image</p>

        <div class="upload-area" id="uploadArea">
            <div class="upload-icon">📤</div>
            <div class="upload-text">Click or drag image here</div>
            <div class="upload-hint">JPG, PNG, WEBP, GIF</div>
        </div>

        <input type="file" id="fileInput" accept="image/jpeg,image/jpg,image/png,image/webp,image/gif">

        <div class="preview-container" id="previewContainer">
            <img id="previewImage" class="preview-image" alt="Preview">
            <div class="file-info" id="fileInfo"></div>
            <button class="btn btn-upload" id="uploadBtn">
                Upload Image
            </button>
            <button class="btn btn-clear" id="clearBtn">
                Clear Selection
            </button>
        </div>

        <div class="progress-bar" id="progressBar">
            <div class="progress-fill" id="progressFill"></div>
        </div>

        <div class="message" id="message"></div>
    </div>

    <script>
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const previewContainer = document.getElementById('previewContainer');
        const previewImage = document.getElementById('previewImage');
        const fileInfo = document.getElementById('fileInfo');
        const uploadBtn = document.getElementById('uploadBtn');
        const clearBtn = document.getElementById('clearBtn');
        const progressBar = document.getElementById('progressBar');
        const progressFill = document.getElementById('progressFill');
        const messageEl = document.getElementById('message');

        let selectedFile = null;

        // Click to select file
        uploadArea.addEventListener('click', () => fileInput.click());

        // File selection
        fileInput.addEventListener('change', handleFileSelect);

        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragging');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragging');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragging');

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect();
            }
        });

        function handleFileSelect() {
            const file = fileInput.files[0];
            if (!file) return;

            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                showMessage('Please select a valid image file (JPG, PNG, WEBP, GIF)', 'error');
                return;
            }

            selectedFile = file;

            // Show preview
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImage.src = e.target.result;
                previewContainer.classList.add('active');
                uploadArea.style.display = 'none';

                const sizeKB = (file.size / 1024).toFixed(2);
                fileInfo.textContent = `${file.name} (${sizeKB} KB)`;
            };
            reader.readAsDataURL(file);

            hideMessage();
        }

        // Clear selection
        clearBtn.addEventListener('click', () => {
            selectedFile = null;
            fileInput.value = '';
            previewContainer.classList.remove('active');
            uploadArea.style.display = 'block';
            hideMessage();
        });

        // Upload file via AJAX
        uploadBtn.addEventListener('click', async () => {
            if (!selectedFile) return;

            const formData = new FormData();
            formData.append('image', selectedFile);

            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<span class="spinner"></span> Uploading...';
            progressBar.classList.add('active');
            hideMessage();

            try {
                const xhr = new XMLHttpRequest();

                // Progress tracking
                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) {
                        const percent = (e.loaded / e.total) * 100;
                        progressFill.style.width = percent + '%';
                    }
                });

                // Response handling
                xhr.addEventListener('load', () => {
                    progressBar.classList.remove('active');
                    uploadBtn.disabled = false;
                    uploadBtn.textContent = 'Upload Image';

                    if (xhr.status === 201) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            showMessage('✅ Upload successful!', 'success');

                            // Add links
                            const links = `
                                <div class="result-links">
                                    <a href="${response.data.view_url}" target="_blank">View Lantern</a>
                                    <a href="${response.data.download_url}" target="_blank">Download</a>
                                    <a href="/" target="_blank">Live Feed</a>
                                </div>
                            `;
                            messageEl.innerHTML += links;

                            // Reset form after 5 seconds
                            setTimeout(() => {
                                selectedFile = null;
                                fileInput.value = '';
                                previewContainer.classList.remove('active');
                                uploadArea.style.display = 'block';
                                hideMessage();
                            }, 5000);
                        } catch (e) {
                            console.error('Response:', xhr.responseText);
                            showMessage('Upload completed but response parsing failed. Status: ' + xhr
                                .status, 'error');
                        }
                    } else {
                        try {
                            const error = JSON.parse(xhr.responseText);
                            showMessage('Upload failed: ' + (error.message || 'Unknown error'),
                            'error');
                        } catch (e) {
                            console.error('Error response:', xhr.responseText);
                            showMessage('Upload failed with status ' + xhr.status +
                                '. Check console for details.', 'error');
                        }
                    }
                });

                xhr.addEventListener('error', () => {
                    progressBar.classList.remove('active');
                    uploadBtn.disabled = false;
                    uploadBtn.textContent = 'Upload Image';
                    showMessage('Network error. Please try again.', 'error');
                });

                xhr.open('POST', '{{ url('/api/v1/uploads') }}');
                xhr.send(formData);

            } catch (error) {
                progressBar.classList.remove('active');
                uploadBtn.disabled = false;
                uploadBtn.textContent = 'Upload Image';
                showMessage('Upload failed: ' + error.message, 'error');
            }
        });

        function showMessage(text, type) {
            messageEl.textContent = text;
            messageEl.className = 'message active ' + type;
        }

        function hideMessage() {
            messageEl.className = 'message';
        }
    </script>
</body>

</html>
