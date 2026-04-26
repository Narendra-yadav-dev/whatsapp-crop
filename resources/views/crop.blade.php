<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crop Image</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <style>
        body {
            background: #181818;
            color: #fff;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .modal-bg {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.85);
            z-index: 1000;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            overflow-y: auto;
        }
        .modal-content {
            background: #23272b;
            padding: 2rem 1.5rem 1.5rem 1.5rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
            min-width: 340px;
            max-width: 95vw;
            text-align: center;
            margin: 2rem 0;
            border-radius: 16px;
        }
        .cropper-area {
            width: 320px;
            height: 320px;
            margin: 0 auto 1.5rem auto;
            position: relative;
            background: #111;
            overflow: hidden;
            box-shadow: 0 0 0 4px #25d366;
        }
        .cropper-area img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .circle-mask {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            box-shadow: 0 0 0 9999px rgba(24,24,24,0.85);
            pointer-events: none;
        }
        .btn {
            background: #25d366;
            color: #fff;
            border: none;
            padding: 0.7rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            margin: 0.5rem 0.5rem 0 0.5rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn:active, .btn:hover {
            background: #1fa855;
        }
        .file-input {
            margin-bottom: 1.5rem;
            color: #fff;
        }
        @media (max-width: 400px) {
            .modal-content, .cropper-area { min-width: 90vw; width: 90vw; height: 90vw; }
        }
    </style>
</head>
<body>
    <div class="modal-bg">
        <div class="modal-content">
            <h2 style="margin-bottom:1rem;">Set Profile Photo</h2>
            <input type="file" class="file-input" name="image" id="imageInput" accept="image/*" required>
            <div class="cropper-area">
                <img id="previewImage" style="display:none;" />
                <div class="circle-mask"></div>
            </div>
            <button id="cropBtn" type="button" class="btn" style="display:none;">Crop & Upload</button>
            <form id="uploadForm" method="POST" enctype="multipart/form-data" style="display:none;">
                @csrf
                <input type="hidden" name="cropped_image" id="croppedImage">
            </form>
            <div id="uploadedResult" style="margin-top:2rem;"></div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let cropper;
        const image = document.getElementById('previewImage');
        const imageInput = document.getElementById('imageInput');
        const cropBtn = document.getElementById('cropBtn');
        imageInput.addEventListener('change', function(e){
            let files = e.target.files;
            let done = function(url){
                image.src = url;
                image.style.display = 'block';
                cropBtn.style.display = 'inline-block';
                if(cropper){ cropper.destroy(); }
                image.onload = function() {
                    if (typeof Cropper !== 'undefined') {
                        cropper = new Cropper(image, {
                            aspectRatio: 1,
                            viewMode: 1,
                            movable: true,
                            zoomable: true,
                            scalable: true,
                            rotatable: true,
                            dragMode: 'move',
                            background: false,
                            guides: false,
                            highlight: false,
                            cropBoxMovable: false,
                            cropBoxResizable: false,
                            minContainerWidth: 320,
                            minContainerHeight: 320,
                            ready() {
                                // Make crop box circular
                                // const cropBox = document.querySelector('.cropper-crop-box');
                                // if (cropBox) cropBox.style.borderRadius = '50%';
                                // const viewBox = document.querySelector('.cropper-view-box');
                                // if (viewBox) viewBox.style.borderRadius = '50%';
                            }
                        });
                    } else {
                        alert('Cropper.js failed to load!');
                    }
                };
            };
            let reader, file;
            if(files && files.length > 0){
                file = files[0];
                if(URL){
                    done(URL.createObjectURL(file));
                }else{
                    reader = new FileReader();
                    reader.onload = function(e){ done(reader.result); };
                    reader.readAsDataURL(file);
                }
            }
        });
        cropBtn.addEventListener('click', function(){
            if (!cropper) return;
            const canvas = cropper.getCroppedCanvas({ width: 500, height: 500, imageSmoothingQuality: 'high' });
            let base64 = canvas.toDataURL('image/jpeg');
            document.getElementById('croppedImage').value = base64;
            fetch("{{ route('upload.image') }}", {
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ cropped_image: base64 })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success && data.image) {
                    const url = `{{ asset('public/storage/uploads/${data.image}') }}`;
                    document.getElementById('uploadedResult').innerHTML = `<div style='margin-top:1rem;'><strong>Uploaded Image:</strong><br><img src='${url}' style='margin-top:0.5rem;max-width:200px;box-shadow:0 2px 8px #0003;'></div>`;
                } else {
                    alert("Upload failed");
                }
            });
        });
    });
    </script>
</body>
</html>
