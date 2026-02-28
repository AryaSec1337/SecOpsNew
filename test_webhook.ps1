$uri = "http://localhost:8000/api/webhook/file-scan"

$filePath = "d:\SecOps New\test_invoice.txt"
$fileId = "8f91d7c4-4b7e-4e3c-91f1-8d77a6d2b1e2"
$sha256 = "b94d27b9934d3e08a52e52d7da7dabfac484efe37a5380ee9088f7ace2efcde9" # fake hash
$sizeBytes = 28
$originalFilename = "test_invoice.txt"
$uploadedAt = "2026-02-27T14:22:10+07:00"
$endpoint = "/api/upload/claim"

$boundary = [System.Guid]::NewGuid().ToString()

$bodyArgs = @{
    "file_id" = $fileId
    "sha256" = $sha256
    "size_bytes" = $sizeBytes
    "original_filename" = $originalFilename
    "uploaded_at" = $uploadedAt
    "endpoint" = $endpoint
}

$contentType = "multipart/form-data; boundary=`"$boundary`""
$stream = [System.IO.MemoryStream]::new()
$writer = [System.IO.StreamWriter]::new($stream)

foreach ($key in $bodyArgs.Keys) {
    $writer.Write("--$boundary`r`n")
    $writer.Write("Content-Disposition: form-data; name=`"$key`"`r`n`r`n")
    $writer.Write($bodyArgs[$key])
    $writer.Write("`r`n")
}

$writer.Write("--$boundary`r`n")
$writer.Write("Content-Disposition: form-data; name=`"file`"; filename=`"test_invoice.txt`"`r`n")
$writer.Write("Content-Type: text/plain`r`n`r`n")
$writer.Flush()

$fileBytes = [System.IO.File]::ReadAllBytes($filePath)
$stream.Write($fileBytes, 0, $fileBytes.Length)

$writer.Write("`r`n")
$writer.Write("--$boundary--`r`n")
$writer.Flush()
$stream.Position = 0

try {
    $response = Invoke-RestMethod -Uri $uri -Method Post -ContentType $contentType -Body $stream
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Error $_.Exception.Message
    if ($_.ErrorDetails) {
        Write-Error $_.ErrorDetails.Message
    }
}
