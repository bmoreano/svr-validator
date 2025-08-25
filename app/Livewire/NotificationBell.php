<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class NotificationBell extends Component
{
    /**
     * Escuchadores de eventos. '$refresh' es una acción mágica que le dice
     * a Livewire que vuelva a renderizar este componente.
     */
    protected $listeners = ['notificationReceived' => '$refresh'];

    /**
     * Marca una notificación específica como leída y redirige si tiene un enlace.
     */
    public function markAsRead(string $notificationId)
    {
        $notification = Auth::user()->notifications()->find($notificationId);
        if ($notification) {
            $notification->markAsRead();
            
            if (isset($notification->data['link'])) {
                return redirect($notification->data['link']);
            }
        }
    }

    /**
     * Marca todas las notificaciones no leídas como leídas.
     * Esta acción es llamada por el botón "Marcar todas como leídas".
     * Después de que este método se ejecute, Livewire automáticamente
     * llamará de nuevo al método render() para actualizar la vista.
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    /**
     * Renderiza la vista del componente.
     * Obtiene los datos más recientes de la base de datos en cada renderizado.
     */
    public function render()
    {
        // Esta consulta se ejecuta cada vez que el componente se actualiza.
        // Después de markAllAsRead, esta línea obtendrá 0.
        $unreadCount = Auth::user()->unreadNotifications()->count();
        
        // Obtenemos las notificaciones para mostrarlas.
        $notifications = Auth::user()->notifications()->latest()->take(10)->get();
        
        return view('livewire.notification-bell', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
}