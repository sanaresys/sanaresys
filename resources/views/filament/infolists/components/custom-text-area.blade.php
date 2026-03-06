@php
    $content = $getContent();
    $label = $getLabel();
    $placeholder = $getCustomPlaceholder();
@endphp

<div class="mb-6">
    @if($label)
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
            {{ $label }}
        </h3>
    @endif
    
    <div class="relative group">
        <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg p-4 min-h-[100px] transition-colors duration-200">
            @if($content)
                <div class="text-gray-900 dark:text-gray-100 whitespace-pre-line text-left leading-relaxed font-normal text-sm">
                    {!! nl2br(e($content)) !!}
                </div>
                
                <!-- Botón de copiar -->
                <button 
                    type="button"
                    onclick="copyToClipboard(this)"
                    data-content="{{ $content }}"
                    class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 p-2 rounded-md text-xs"
                    title="Copiar texto"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </button>
            @else
                <div class="text-gray-500 dark:text-gray-400 italic text-sm">
                    {{ $placeholder ?: 'Sin información' }}
                </div>
            @endif
        </div>
    </div>
</div>

@once
<script>
function copyToClipboard(button) {
    const content = button.getAttribute('data-content');
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(content).then(() => {
            showCopySuccess(button);
        }).catch(() => {
            fallbackCopy(content, button);
        });
    } else {
        fallbackCopy(content, button);
    }
}

function fallbackCopy(text, button) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.opacity = '0';
    document.body.appendChild(textArea);
    textArea.select();
    
    try {
        document.execCommand('copy');
        showCopySuccess(button);
    } catch (err) {
        console.error('Error al copiar:', err);
    }
    
    document.body.removeChild(textArea);
}

function showCopySuccess(button) {
    const originalContent = button.innerHTML;
    button.innerHTML = `
        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
    `;
    button.classList.add('text-green-600', 'bg-green-100', 'dark:bg-green-900');
    
    setTimeout(() => {
        button.innerHTML = originalContent;
        button.classList.remove('text-green-600', 'bg-green-100', 'dark:bg-green-900');
    }, 2000);
}
</script>
@endonce
