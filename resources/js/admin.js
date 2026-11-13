import Alpine from 'alpinejs';
import 'trix';
import 'trix/dist/trix.css';

window.Alpine = Alpine;

// Trix: no file attachments (images go through the Media screen).
document.addEventListener('trix-file-accept', (e) => e.preventDefault());

// Repeatable rows (highlights, itinerary, FAQs...)
Alpine.data('repeater', (items = []) => ({
    items,
    add(blank = {}) { this.items.push({ ...blank }); },
    remove(i) { this.items.splice(i, 1); },
    up(i) { if (i > 0) this.items.splice(i - 1, 0, this.items.splice(i, 1)[0]); },
    down(i) { if (i < this.items.length - 1) this.items.splice(i + 1, 0, this.items.splice(i, 1)[0]); },
}));

Alpine.start();
