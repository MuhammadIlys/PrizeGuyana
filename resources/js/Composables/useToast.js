import { ref } from 'vue';

const toasts = ref([]);

export function useToast() {
    const addToast = (message, type = 'info', timeout = 3500) => {
        const id = Date.now();
        toasts.value.push({ id, message, type });

        setTimeout(() => {
            removeToast(id); 
        }, timeout);
    };

    const removeToast = (id) => {
        toasts.value = toasts.value.filter(toast => toast.id !== id);
    };

    return {
        toasts,
        addToast,
        removeToast,
        warning: (msg) => addToast(msg, 'warning'),
        success: (msg) => addToast(msg, 'success'),
        error: (msg) => addToast(msg, 'error'),
        info: (msg) => addToast(msg, 'info'),
    };
}