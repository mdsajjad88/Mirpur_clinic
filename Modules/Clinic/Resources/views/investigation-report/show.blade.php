@extends('clinic::layouts.app2')
@php
$title='Show Invoice';
$subTitle = 'Show Invoice';
@endphp
@section('content')
<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
}

.invoice-header {
    color: #0d6efd;
    font-weight: bold;
}

.table-header {
    background-color: #28a745;
    color: white;
}

.table-subtotal {
    background-color: #e9ecef;
    font-weight: bold;
}
</style>

<div class="container">
    <div class="row mb-3">
        <div class="col-6">
            <h4 class="invoice-header">Sell (Invoice No.: {{ $query->invoice_no }})</h4>
        </div>
        <div class="col-6 text-end">
            <p><strong>Date:</strong> {{ $query->transaction_date }} </p>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-4">
            <p><strong>Invoice No.:</strong> {{ $query->invoice_no }} </p>
            <p><strong>Status:</strong> {{ $query->status }} </p>
            <p><strong>Payment Status:</strong> {{ $query->payment_status }} </p>
            <p><strong>Reference:</strong> {{ $query->dr_name}} </p>
        </div>
        <div class="col-4">
            <p><strong>Customer name:</strong> {{ $query->name }} </p>
            <p><strong>Address:</strong> {{ $query->address_line_1 }} </p>
        </div>
    </div>

    <h5>Products:</h5>
    <table class="table table-bordered mb-20">
        <thead class="table-header">
            <tr>
                <th scope="col">S.L</th>
                <th scope="col">Product</th>
                <th scope="col">SKU</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
            <tr>
                <td>
                    @php
                        // Check if this product has an associated PDF report
                        $hasPdf = false;
                        foreach ($pdf_report as $report) {
                            $reportProducts = json_decode($report->product_name, true);
                            if (is_array($reportProducts) && in_array($product->product_name, $reportProducts)) {
                                $hasPdf = true;
                                break;
                            }
                        }
                    @endphp
                    @if (!$hasPdf)
                        <div class="form-check style-check d-flex align-items-center">
                            <input class="form-check-input product-checkbox" type="checkbox" value="{{ $product->product_name }}" id="check_{{ $product->product_name }}">
                            <label class="form-check-label" for="check_{{ $product->product_name }}">
                                {{ $product->product_id }}
                            </label>
                        </div>
                    @else
                        {{ $product->product_id }}
                    @endif
                    <!-- <div class="form-check style-check d-flex align-items-center">
                        <input class="form-check-input product-checkbox" type="checkbox" value="{{ $product->product_name }}" id="check_{{ $product->product_name }}">
                        <label class="form-check-label" for="check_{{  $product->product_name }}">
                            {{ $product->product_id }}
                        </label>
                    </div>  -->
                </td>
                <td>{{ $product->product_name }}</td>
                <td>{{ $product->sub_sku }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- PDF Upload Form -->
    <div class="row mb-20">
        <h5>Upload PDF Document</h5>
        <div class="col-10">
            <form action="#" id="uploadForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="pdf_file" class="form-label">Select PDF File</label>
                    <input type="file" class="form-control" id="pdf_file" name="pdf_file" accept="application/pdf"
                        required>
                    @error('pdf_file')
                    <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary" id="uploadButton" disabled>Upload</button>
            </form>
        </div>
    </div>

    @if (!empty($pdf_report) && count($pdf_report) > 0 )
        <h5>PDF:</h5>
        <table class="table table-bordered mb-20">
            <thead class="table-header">
                <tr>
                    <th scope="col">Product</th>
                    <th scope="col">PDF</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pdf_report as $item)
                    <tr>
                        <td>
                            @php
                                $products = json_decode($item->product_name, true);
                            @endphp
                            {{ implode(', ', $products) }}
                        </td>
                        <td>@if (!empty($item->report_path))
                                @php
                                    $pdfUrl = asset($item->report_path);
                                @endphp
                                    <a href="{{ $pdfUrl }}" target="_blank">View PDF</a>
                                @else
                                    No file available
                                @endif
                        </td>
                        <td>
                            <a href="#" class="delete-pdf w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center" data-id="{{ $item->id }}" data-url="{{ route('pdf_report_delete', $item->id) }}">
                                <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No PDF reports available.</p>
    @endif
</div>

<script>

// Upload PDF
document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    const uploadButton = document.getElementById('uploadButton');
    const uploadForm = document.getElementById('uploadForm');
    const pdfInput = document.getElementById('pdf_file');

    // Enable/disable upload button based on checkbox selection
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
            uploadButton.disabled = !anyChecked;
        });
    });

    // Handle form submission
    uploadForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const selectedProducts = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        if (selectedProducts.length === 0) {
            showAlert('Please select at least one product.', 'danger');
            return;
        }

        if (!pdfInput.files.length) {
            showAlert('Please select a PDF file to upload.', 'danger');
            return;
        }

        const file = pdfInput.files[0];
        if (file.type !== 'application/pdf') {
            showAlert('Only PDF files are allowed.', 'danger');
            console.error('Invalid file type:', file.type);
            return;
        }

        const formData = new FormData(uploadForm);
        formData.append('product_names', JSON.stringify(selectedProducts));
        formData.append('trans_id', "{{ $query->id }}");

        // console.log(formData.get('product_ids'));
        
        // To log all key-value pairs in the FormData
        // for (let [key, value] of formData.entries()) {
        //     console.log(key, value);
        // }

        // console.log(formData);

        // console.log(selectedProducts);

        // Log all formData key-value pairs
        console.log('Uploading with formData:');
        for (let [key, value] of formData.entries()) {
            console.log(key, value);
        }

        try {
            const response = await fetch('{{ route('invoices.upload-pdf') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const result = await response.json();

            if (response.ok) {
                showAlert('PDF uploaded successfully!', 'success');
                setTimeout(() => location.reload(), 2000); // Reload to update report links
            } else {
                console.error('Upload failed:', result);
                showAlert(result.message || 'Failed to upload PDF.', 'danger');
            }
        } catch (error) {
            console.error('Upload error:', error);
            showAlert('An error occurred during upload.', 'danger');
        }
    });

    // Show alert message
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.role = 'alert';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 5000);
    }
});

// Delete PDF
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-pdf').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();

            const url = this.getAttribute('data-url');
            const id = this.getAttribute('data-id');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!csrfToken) {
                console.error('CSRF token not found in meta tag.');
                alert('Security token missing. Please reload the page.');
                return;
            }

            if (!url || !id) {
                console.error('Missing URL or ID in data attributes.');
                return;
            }

            if (confirm('Are you sure you want to delete this PDF report?')) {
                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.message) {
                        alert(data.message);
                        const reportElement = document.getElementById(`pdf-report-${id}`);
                        if (reportElement) {
                            reportElement.remove();
                        }
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        alert('Unexpected response from the server.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete PDF report.');
                });
            }
        });
    });
});

</script>

@endsection