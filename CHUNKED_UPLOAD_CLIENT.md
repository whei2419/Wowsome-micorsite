# Chunked Video Upload - Client Implementation

## Problem
The current upload tries to send the entire video file (~14MB) in one request, causing `UPLOAD_ERR_PARTIAL` errors. Additionally, multipart file uploads are failing due to network/proxy issues between client and server.

## Solution
Use **base64-encoded chunks** sent as JSON to avoid multipart/form-data issues entirely.

## Client-Side Implementation (Vue.js / JavaScript)

Replace the current video upload logic with this reliable base64 chunked upload:

```javascript
/**
 * Upload video file using base64-encoded chunks (bypasses multipart issues)
 * @param {File} file - The video file to upload
 * @param {string} apiBaseUrl - Base URL (e.g., 'https://glassbooth.wowbynow.com.my/api')
 * @param {Function} onProgress - Optional progress callback (receives percentage 0-100)
 * @returns {Promise<Object>} Response with url, path
 */
async function uploadVideoChunked(file, apiBaseUrl, onProgress = null) {
  const CHUNK_SIZE = 1 * 1024 * 1024; // 1MB chunks
  const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
  const uploadId = `vid_${Date.now()}${Math.floor(Math.random() * 1000000000)}`;
  
  console.log(`[chunked upload] file: ${file.name}, size: ${(file.size / 1024 / 1024).toFixed(2)}MB, chunks: ${totalChunks}`);
  
  // Helper to convert Blob to base64
  const blobToBase64 = (blob) => {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onloadend = () => {
        const base64 = reader.result.split(',')[1]; // Remove data:...;base64, prefix
        resolve(base64);
      };
      reader.onerror = reject;
      reader.readAsDataURL(blob);
    });
  };
  
  // Upload each chunk
  for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
    const start = chunkIndex * CHUNK_SIZE;
    const end = Math.min(start + CHUNK_SIZE, file.size);
    const chunkBlob = file.slice(start, end);
    
    console.log(`[chunk ${chunkIndex + 1}/${totalChunks}] encoding ${(chunkBlob.size / 1024).toFixed(1)}KB...`);
    
    // Convert chunk to base64
    const chunkBase64 = await blobToBase64(chunkBlob);
    
    console.log(`[chunk ${chunkIndex + 1}/${totalChunks}] uploading...`);
    
    // Send as JSON (not multipart)
    const response = await fetch(`${apiBaseUrl}/upload-video/chunk`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',  // ← JSON, not multipart
      },
      body: JSON.stringify({
        upload_id: uploadId,
        chunk_index: chunkIndex,
        filename: file.name,
        chunk_data: chunkBase64,  // ← base64-encoded chunk
      })
    });
    
    if (!response.ok) {
      const error = await response.json().catch(() => ({}));
      throw new Error(`Chunk ${chunkIndex} failed: ${response.status} - ${JSON.stringify(error)}`);
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
  
  const assembleResponse = await fetch(`${apiBaseUrl}/upload-video/assemble`, {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      upload_id: uploadId,
      total_chunks: totalChunks,
      filename: file.name,
    })
  });
  
  if (!assembleResponse.ok) {
    const error = await assembleResponse.json().catch(() => ({}));
    throw new Error(`Assembly failed: ${assembleResponse.status} - ${JSON.stringify(error)}`);
  }
  
  const result = await assembleResponse.json();
  console.log(`[chunked upload] success! url: ${result.url}`);
  
  return result; // { ok: true, path: '...', url: '...' }
}

// Usage in your App.vue:
async onRecordSaved(recordData) {
  try {
    const videoPath = recordData.path;
    const file = await getFileFromPath(videoPath); // Your file retrieval logic
    
    console.log('[video upload] starting chunked upload (base64), size:', (file.size / 1024 / 1024).toFixed(2) + 'MB');
    
    const result = await uploadVideoChunked(
      file,
      'https://glassbooth.wowbynow.com.my/api',
      (progress) => {
        console.log(`[upload progress] ${progress}%`);
      }
    );
    
    console.log('[video upload] success:', result.url);
    
  } catch (error) {
    console.error('[video upload] failed:', error.message);
  }
}
```

## Why Base64 Instead of Multipart?

Your uploads were failing with `UPLOAD_ERR_PARTIAL` (error 3) consistently because:
1. Network instability between client and server
2. Possible proxy/firewall interference
3. Client-side timeout issues

**Base64 advantages:**
- ✅ Bypasses multipart/form-data parsing issues
- ✅ Sent as simple JSON (more reliable)
- ✅ Better error handling
- ✅ No file parsing required on server

**Trade-offs:**
- ~33% larger payload (base64 encoding overhead)
- But 1MB chunks are still small enough to be reliable

## Backend Status

✅ Backend already supports both methods:
- Multipart file upload (for when it works)
- Base64 JSON upload (fallback for problematic connections)

The backend automatically detects which format is being used.
