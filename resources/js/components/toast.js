export function globalToast({ message = '', type = 'success', duration = 3000 }) {
    const container = document.createElement('div');
    container.className = `fixed bottom-4 right-4 z-50 px-4 py-2 rounded shadow text-white transition-opacity duration-300 opacity-0 ${
        type === 'success' ? 'bg-green-500' :
        type === 'error' ? 'bg-red-500' :
        type === 'warning' ? 'bg-yellow-400 text-black' :
        'bg-gray-800'
    }`;
    container.textContent = message;
    document.body.appendChild(container);

    requestAnimationFrame(() => container.classList.remove('opacity-0'));

    setTimeout(() => {
        container.classList.add('opacity-0');
        setTimeout(() => container.remove(), 300);
    }, duration);
}
