# Chunked Video Upload - Client Implementation

## Problem
The current upload tries to send the entire video file (~14MB) in one request, causing `UPLOAD_ERR_PARTIAL` errors due to network instability or timeout issues.

## Solution
Use the existing chunked upload endpoints to split large files into smaller chunks.

## Client-Side Implementation (Vue.js)

Replace the current `onRecordSaved` video upload logic with this chunked upload function:

```javascript
/**
 * Upload video file using chunked upload for reliability
 * @param {File} file - The video file to upload
 * @param {string} apiBaseUrl - Base URL (e.g., 'https://glassbooth.wowbynow.com.my/api')
 * @param {Function} onProgress - Optional progress callback (receives percentage 0-100)
 * @returns {Promise<Object>} Response with url, path
 */
async function uploadVideoChunked(file, apiBaseUrl, onProgress = null) {
  const CHUNK_SIZE = 1 * 1024 * 1024; // 1MB chunks (adjust as needed)
  const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
  const uploadId = `upload_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  
  console.log(`[chunked upload] file: ${file.name}, size: ${(file.size / 1024 / 1024).toFixed(2)}MB, chunks: ${totalChunks}`);
  
  // Upload each chunk
  for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
    const start = chunkIndex * CHUNK_SIZE;
    const end = Math.min(start + CHUNK_SIZE, file.size);
    const chunk = file.slice(start, end);
    
    const formData = new FormData();
    formData.append('file', chunk);
    formData.append('upload_id', uploadId);
    formData.append('chunk_index', chunkIndex);
    formData.append('filename', file.name);
    
    console.log(`[chunk ${chunkIndex + 1}/${totalChunks}] uploading ${(chunk.size / 1024).toFixed(1)}KB...`);
    
    const response = await fetch(`${apiBaseUrl}/upload-video/chunk`, {
      method: 'POST',
      body: formData,
      headers: {
        'Accept': 'application/json'
      }
    });
    
    if (!response.ok) {
      const error = await response.json().catch(() => ({}));
      throw new Error(`Chunk ${chunkIndex} failed: ${response.status} ${response.statusText} - ${JSON.stringify(error)}`);
    }
    
    const result = await response.json();
    console.log(`[chunk ${chunkIndex + 1}/${totalChunks}] uploaded successfully`);
    
    // Report progress
    if (onProgress) {
      const progress = Math.round(((chunkIndex + 1) / totalChunks) * 100);
      onProgress(progress);
    }
  }
  
  // Assemble all chunks
  console.log(`[chunked upload] assembling ${totalChunks} chunks...`);
  
  const assembleData = new FormData();
  assembleData.append('upload_id', uploadId);
  assembleData.append('total_chunks', totalChunks);
  assembleData.append('filename', file.name);
  
  const assembleResponse = await fetch(`${apiBaseUrl}/upload-video/assemble`, {
    method: 'POST',
    body: assembleData,
    headers: {
      'Accept': 'application/json'
    }
  });
  
  if (!assembleResponse.ok) {
    const error = await assembleResponse.json().catch(() => ({}));
    throw new Error(`Assembly failed: ${assembleResponse.status} ${assembleResponse.statusText} - ${JSON.stringify(error)}`);
  }
  
  const result = await assembleResponse.json();
  console.log(`[chunked upload] success! url: ${result.url}`);
  
  return result; // { ok: true, path: '...', url: '...' }
}

// Usage in your App.vue onRecordSaved method:
async onRecordSaved(recordData) {
  try {
    // Get the video file from filesystem (your existing logic)
    const videoPath = recordData.path; // e.g., 'C:/Users/whei/Videos/obs-test/2026-04-28 13-20-27.mp4'
    const file = await getFileFromPath(videoPath); // Your existing file retrieval logic
    
    console.log('[video upload] path:', videoPath, '| url:', 'https://glassbooth.wowbynow.com.my/api/upload-video');
    console.log('[video upload] using chunked upload, size:', (file.size / 1024 / 1024).toFixed(2) + 'MB');
    
    // Use chunked upload instead of single upload
    const result = await uploadVideoChunked(
      file,
      'https://glassbooth.wowbynow.com.my/api',
      (progress) => {
        console.log(`[upload progress] ${progress}%`);
        // You can update a progress bar here
      }
    );
    
    console.log('[video upload] success:', result.url);
    
  } catch (error) {
    console.error('[video upload] failed:', error.message);
  }
}
```

## Key Benefits

1. **Reliability**: Each chunk is only 1MB, uploading quickly
2. **Resume capability**: Can retry individual chunks on failure
3. **Progress tracking**: See upload progress in real-time
4. **Network resilience**: Small chunks less affected by network instability

## Configuration

Adjust `CHUNK_SIZE` based on your network:
- **Fast network**: 2-5MB chunks
- **Slow/unstable network**: 512KB-1MB chunks
- **Mobile/3G**: 256KB-512KB chunks

## Backend Status

✅ Already implemented and working:
- `POST /api/upload-video/chunk` - Upload individual chunks
- `POST /api/upload-video/assemble` - Assemble chunks into final video
- Fires `VideoUploaded` event after successful assembly
