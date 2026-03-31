@props([
    'id' => 'livewire-toast',
])

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100">
    <div id="{{ $id }}" class="toast align-items-center border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true" wire:ignore>
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i id="{{ $id }}-icon" class="bi"></i>
                <span id="{{ $id }}-message" class="fw-medium"></span>
            </div>
            <button id="{{ $id }}-close" type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
    window.addEventListener('show-toast', event => {
        const toastId = '{{ $id }}';
        const toastElement = document.getElementById(toastId);
        const messageElement = document.getElementById(toastId + '-message');
        const iconElement = document.getElementById(toastId + '-icon');
        const closeBtn = document.getElementById(toastId + '-close');
        
        const type = event.detail.type || 'success';

        messageElement.innerText = event.detail.message;

        toastElement.className = `toast align-items-center border-0 shadow text-white bg-${type}`;

        if (['success', 'danger', 'primary', 'dark'].includes(type)) {
            closeBtn.classList.add('btn-close-white');
        } else {
            closeBtn.classList.remove('btn-close-white');
        }

        iconElement.className = `bi ${type === 'danger' ? 'bi-exclamation-circle' : 'bi-check-circle'} fs-5`;

        const toast = new bootstrap.Toast(toastElement, { 
            delay: 3000,
            autohide: true 
        });
        toast.show();
    });
</script>