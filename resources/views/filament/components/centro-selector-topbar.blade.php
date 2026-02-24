@auth
    @if(auth()->user()?->hasRole('root'))
        <div class="flex items-center space-x-4">
            @livewire('centro-selector')
        </div>
    @endif
@endauth
