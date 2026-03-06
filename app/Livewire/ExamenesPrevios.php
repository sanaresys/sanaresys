<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Examenes;
use App\Models\Pacientes;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class ExamenesPrevios extends Component
{
    use WithFileUploads;

    public $paciente_id;
    public $examenes_previos = [];
    public $imagenes = [];
    public $mostrarAccordion = false;

    protected $listeners = ['refreshExamenes'];

    public function mount($paciente_id = null)
    {
        $this->paciente_id = $paciente_id;
        $this->cargarExamenesPrevios();
    }

    public function cargarExamenesPrevios()
    {
        if ($this->paciente_id) {
            $this->examenes_previos = Examenes::examenesPrevios($this->paciente_id)
                ->with(['medico', 'medico.user'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();
            
            $this->mostrarAccordion = count($this->examenes_previos) > 0;
        }
    }

    public function subirImagen($examen_id)
    {
        $examen = Examenes::find($examen_id);
        
        if (!$examen || !$examen->puedeSubirImagen()) {
            Notification::make()
                ->title('Error')
                ->body('No se puede subir imagen para este examen')
                ->danger()
                ->send();
            return;
        }

        if (!isset($this->imagenes[$examen_id])) {
            Notification::make()
                ->title('Error')
                ->body('Debe seleccionar una imagen')
                ->danger()
                ->send();
            return;
        }

        $imagen = $this->imagenes[$examen_id];
        
        // Validar el archivo
        $this->validate([
            "imagenes.{$examen_id}" => 'required|image|max:5120', // 5MB máximo
        ]);

        try {
            // Eliminar imagen anterior si existe
            $examen->eliminarImagenAnterior();

            // Guardar nueva imagen
            $nombreArchivo = 'examen_' . $examen_id . '_' . time() . '.' . $imagen->getClientOriginalExtension();
            $rutaImagen = $imagen->storeAs('public/examenes', $nombreArchivo);
            
            // Completar examen con imagen
            $examen->completarConImagen($nombreArchivo);
            
            // Limpiar el input
            unset($this->imagenes[$examen_id]);
            
            // Recargar datos
            $this->cargarExamenesPrevios();
            
            Notification::make()
                ->title('Éxito')
                ->body('Imagen subida correctamente y examen completado')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Error al subir la imagen: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function marcarNoPresent($examen_id)
    {
        $examen = Examenes::find($examen_id);
        
        if (!$examen) {
            Notification::make()
                ->title('Error')
                ->body('Examen no encontrado')
                ->danger()
                ->send();
            return;
        }

        try {
            $examen->marcarNoPresent();
            $this->cargarExamenesPrevios();
            
            Notification::make()
                ->title('Éxito')
                ->body('Examen marcado como "No presentado"')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Error al marcar el examen: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function eliminarImagen($examen_id)
    {
        $examen = Examenes::find($examen_id);
        
        if (!$examen) {
            Notification::make()
                ->title('Error')
                ->body('Examen no encontrado')
                ->danger()
                ->send();
            return;
        }

        try {
            $examen->eliminarImagenAnterior();
            $examen->update(['estado' => 'Solicitado']);
            
            $this->cargarExamenesPrevios();
            
            Notification::make()
                ->title('Éxito')
                ->body('Imagen eliminada correctamente')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Error al eliminar la imagen: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function verImagen($rutaImagen)
    {
        if (Storage::exists('public/examenes/' . $rutaImagen)) {
            return Storage::url('public/examenes/' . $rutaImagen);
        }
        return null;
    }

    public function refreshExamenes()
    {
        $this->cargarExamenesPrevios();
    }

    public function render()
    {
        return view('livewire.examenes-previos');
    }
}
