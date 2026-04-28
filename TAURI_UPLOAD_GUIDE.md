# Tauri Video Upload Implementation Guide

**Date:** April 28, 2026  
**Status:** Ready for Windows app implementation  
**Backend:** ✅ Fully configured and tested

---

## 🎯 Problem Summary

**tauri-plugin-upload with multipart/form-data is failing:**
- UPLOAD_ERR_PARTIAL (error 3) on all attempts
- Data never reaches PHP completely
- Client-side issue with how Tauri sends multipart data

**Solution:** Use base64 JSON uploads instead (no special plugin needed)

---

## ✅ Backend Endpoints (Ready)

### 1. Upload Video Chunk
```
POST /api/upload-video/chunk
Content-Type: application/json
```

**Request Body:**
```json
{
  "upload_id": "vid_1234567890",
  "chunk_index": 0,
  "total_chunks": 10,
  "filename": "video.mp4",
  "chunk_data": "<base64-encoded-binary>"
}
```

**Success Response (200):**
```json
{
  "message": "Chunk uploaded successfully",
  "chunk_index": 0,
  "upload_id": "vid_1234567890"
}
```

**Error Response (422):**
```json
{
  "error": "invalid_upload_id",
  "message": "Upload ID is required"
}
```

---

### 2. Assemble Final Video
```
POST /api/upload-video/assemble
Content-Type: application/json
```

**Request Body:**
```json
{
  "upload_id": "vid_1234567890",
  "total_chunks": 10,
  "filename": "video.mp4"
}
```

**Success Response (200):**
```json
{
  "message": "Video assembled successfully",
  "video_url": "/storage/videos/video.mp4",
  "path": "videos/video.mp4"
}
```

---

## 🛠️ Tauri Implementation

### Remove tauri-plugin-upload Dependency

The plugin is not needed. Use standard Tauri filesystem and HTTP instead.

**Cargo.toml:**
```toml
# REMOVE this:
# tauri-plugin-upload = "..."

# Use built-in features instead:
[dependencies]
tauri = { version = "1.x", features = ["fs-all", "http-all"] }
base64 = "0.21"
```

---

### Rust Backend Implementation

**src-tauri/src/main.rs:**

```rust
use tauri::{command, State};
use std::fs::File;
use std::io::Read;
use base64::{Engine as _, engine::general_purpose};

#[command]
async fn upload_video_chunked(
    file_path: String,
    upload_id: String,
    filename: String,
) -> Result<String, String> {
    const CHUNK_SIZE: usize = 1_048_576; // 1MB chunks
    
    // Read file
    let mut file = File::open(&file_path)
        .map_err(|e| format!("Failed to open file: {}", e))?;
    
    let mut buffer = vec![0u8; CHUNK_SIZE];
    let mut chunk_index = 0;
    let mut total_chunks = 0;
    
    // Calculate total chunks
    let file_size = file.metadata()
        .map_err(|e| format!("Failed to get file metadata: {}", e))?
        .len();
    total_chunks = ((file_size as f64) / (CHUNK_SIZE as f64)).ceil() as usize;
    
    // Upload each chunk
    loop {
        let bytes_read = file.read(&mut buffer)
            .map_err(|e| format!("Failed to read file: {}", e))?;
        
        if bytes_read == 0 {
            break; // End of file
        }
        
        // Encode chunk to base64
        let chunk_data = general_purpose::STANDARD.encode(&buffer[..bytes_read]);
        
        // Prepare JSON payload
        let payload = serde_json::json!({
            "upload_id": upload_id,
            "chunk_index": chunk_index,
            "total_chunks": total_chunks,
            "filename": filename,
            "chunk_data": chunk_data
        });
        
        // Send chunk
        let client = reqwest::Client::new();
        let response = client
            .post("https://glassbooth.wowbynow.com.my/api/upload-video/chunk")
            .header("Content-Type", "application/json")
            .json(&payload)
            .send()
            .await
            .map_err(|e| format!("Failed to send chunk {}: {}", chunk_index, e))?;
        
        if !response.status().is_success() {
            let error_text = response.text().await.unwrap_or_default();
            return Err(format!("Chunk {} failed: {}", chunk_index, error_text));
        }
        
        chunk_index += 1;
    }
    
    // Assemble video on server
    let client = reqwest::Client::new();
    let assemble_payload = serde_json::json!({
        "upload_id": upload_id,
        "total_chunks": total_chunks,
        "filename": filename
    });
    
    let response = client
        .post("https://glassbooth.wowbynow.com.my/api/upload-video/assemble")
        .header("Content-Type", "application/json")
        .json(&assemble_payload)
        .send()
        .await
        .map_err(|e| format!("Failed to assemble video: {}", e))?;
    
    if response.status().is_success() {
        let result: serde_json::Value = response.json().await
            .map_err(|e| format!("Failed to parse response: {}", e))?;
        Ok(result["video_url"].as_str().unwrap_or("").to_string())
    } else {
        let error_text = response.text().await.unwrap_or_default();
        Err(format!("Assemble failed: {}", error_text))
    }
}

fn main() {
    tauri::Builder::default()
        .invoke_handler(tauri::generate_handler![upload_video_chunked])
        .run(tauri::generate_context!())
        .expect("error while running tauri application");
}
```

---

### Frontend (JavaScript/TypeScript)

**Upload function:**

```typescript
import { invoke } from '@tauri-apps/api/tauri';

async function uploadVideo(filePath: string, filename: string) {
  try {
    // Generate unique upload ID
    const uploadId = `vid_${Date.now()}${Math.random().toString(36).substr(2, 9)}`;
    
    // Call Rust backend to upload
    const videoUrl = await invoke('upload_video_chunked', {
      filePath: filePath,
      uploadId: uploadId,
      filename: filename
    });
    
    console.log('Video uploaded successfully:', videoUrl);
    return videoUrl;
    
  } catch (error) {
    console.error('Upload failed:', error);
    throw error;
  }
}

// Usage
const videoPath = 'C:\\Users\\...\\video.mp4';
uploadVideo(videoPath, 'video.mp4')
  .then(url => console.log('Success!', url))
  .catch(err => console.error('Error:', err));
```

---

### Alternative: Pure JavaScript Implementation

If you prefer to handle everything in JavaScript (without Rust backend):

```typescript
async function uploadVideoChunked(file: File) {
  const CHUNK_SIZE = 1 * 1024 * 1024; // 1MB
  const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
  const uploadId = `vid_${Date.now()}${Math.random().toString(36).substr(2, 9)}`;
  
  // Upload each chunk
  for (let i = 0; i < totalChunks; i++) {
    const start = i * CHUNK_SIZE;
    const end = Math.min(start + CHUNK_SIZE, file.size);
    const chunk = file.slice(start, end);
    
    // Read chunk as base64
    const base64Data = await new Promise<string>((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = () => {
        const result = reader.result as string;
        // Remove "data:video/mp4;base64," prefix
        const base64 = result.split(',')[1];
        resolve(base64);
      };
      reader.onerror = reject;
      reader.readAsDataURL(chunk);
    });
    
    // Upload chunk
    const response = await fetch('https://glassbooth.wowbynow.com.my/api/upload-video/chunk', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        upload_id: uploadId,
        chunk_index: i,
        total_chunks: totalChunks,
        filename: file.name,
        chunk_data: base64Data
      })
    });
    
    if (!response.ok) {
      const error = await response.json();
      throw new Error(`Chunk ${i} failed: ${error.message}`);
    }
    
    console.log(`Uploaded chunk ${i + 1}/${totalChunks}`);
  }
  
  // Assemble video
  const assembleResponse = await fetch('https://glassbooth.wowbynow.com.my/api/upload-video/assemble', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      upload_id: uploadId,
      total_chunks: totalChunks,
      filename: file.name
    })
  });
  
  if (!assembleResponse.ok) {
    const error = await assembleResponse.json();
    throw new Error(`Assembly failed: ${error.message}`);
  }
  
  const result = await assembleResponse.json();
  return result.video_url;
}

// Usage with file input
const fileInput = document.querySelector('input[type="file"]');
fileInput.addEventListener('change', async (e) => {
  const file = e.target.files[0];
  if (file) {
    try {
      const videoUrl = await uploadVideoChunked(file);
      console.log('Video uploaded:', videoUrl);
    } catch (error) {
      console.error('Upload failed:', error);
    }
  }
});
```

---

## 📊 Progress Tracking

Add progress callbacks to show upload status:

```typescript
interface UploadProgress {
  chunkIndex: number;
  totalChunks: number;
  percentage: number;
}

async function uploadVideoWithProgress(
  file: File,
  onProgress: (progress: UploadProgress) => void
) {
  const CHUNK_SIZE = 1 * 1024 * 1024;
  const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
  const uploadId = `vid_${Date.now()}${Math.random().toString(36).substr(2, 9)}`;
  
  for (let i = 0; i < totalChunks; i++) {
    // ... upload chunk ...
    
    // Report progress
    onProgress({
      chunkIndex: i,
      totalChunks: totalChunks,
      percentage: Math.round(((i + 1) / totalChunks) * 100)
    });
  }
  
  // ... assemble ...
}
```

---

## 🧪 Testing Checklist

- [ ] Small video (< 5MB) uploads successfully
- [ ] Large video (> 10MB) uploads successfully  
- [ ] Progress indicator shows correctly
- [ ] Error handling works (network failure, server error)
- [ ] Retry logic for failed chunks (optional)
- [ ] Can upload multiple videos sequentially

---

## 🔍 Debugging

**Check server logs:**
```bash
tail -f /var/www/glassbooth.wowbynow.com.my/storage/logs/laravel.log
```

**Expected log entries:**
```
[INFO] VideoChunk: request received
[INFO] VideoChunk: chunk stored successfully from base64
[INFO] VideoAssemble: starting assembly
[INFO] VideoAssemble: success
```

**Common issues:**

1. **"invalid upload_id"** → upload_id is missing or empty
2. **"chunk_data not valid base64"** → base64 encoding failed
3. **"chunks missing"** → Not all chunks uploaded before assembly
4. **Network timeout** → Increase chunk timeout on client side

---

## 📝 Configuration

**Recommended settings:**
- Chunk size: **1MB** (1,048,576 bytes)
- Request timeout: **60 seconds per chunk**
- Retry attempts: **3 times per chunk**
- Max concurrent uploads: **1** (sequential)

---

## 🎬 Next Steps

1. **Remove tauri-plugin-upload** from project
2. **Implement Rust backend** with chunked upload function
3. **Update frontend** to call the new Tauri command
4. **Test with sample videos** (various sizes)
5. **Monitor server logs** during testing
6. **Add error handling** and user feedback

---

## 📞 Support

If you encounter issues:
1. Check `/var/www/glassbooth.wowbynow.com.my/storage/logs/laravel.log`
2. Verify base64 encoding is correct (no extra whitespace/newlines)
3. Confirm upload_id is consistent across all chunks
4. Ensure all chunks upload before calling assemble

**Server Status:** ✅ Ready and waiting for uploads  
**Tested:** ✅ Base64 uploads working correctly  
**Documentation:** [SERVER_SPECS_UPLOAD.md](SERVER_SPECS_UPLOAD.md)
