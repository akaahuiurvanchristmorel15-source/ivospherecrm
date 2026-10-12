import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

// Une seule copie d'Alpine pour toute l'application : celle de Livewire, qui la démarre
window.Alpine = Alpine;

Livewire.start();
