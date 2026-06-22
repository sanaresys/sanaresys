<x-filament::page>
    <div id="calendar" style="max-width: 900px; margin: auto;"></div>
</x-filament::page>

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.5/index.global.min.css" rel="stylesheet"/>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.5/index.global.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        console.log(@json($eventos)); // para ver en consola que llegan bien
        let calendar = new FullCalendar.Calendar(
            document.getElementById('calendar'),
            {
                initialView: 'dayGridMonth',
                locale: 'es',
                events: @json($eventos),
            }
        );
        calendar.render();
    });
    </script>
@endpush
