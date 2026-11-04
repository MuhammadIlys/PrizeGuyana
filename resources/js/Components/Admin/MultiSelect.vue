<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    options: {
        type: Array,
        default: () => [],
    },
    labelKey: {
        type: String,
        default: 'title',
    },
    valueKey: {
        type: String,
        default: 'id',
    },
    subLabelKey: {
        type: String,
        default: null,
    },
    placeholder: {
        type: String,
        default: 'Select options...',
    },
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const searchQuery = ref('');
const dropdownRef = ref(null);

// Filter options based on search query
const filteredOptions = computed(() => {
    const search = searchQuery.value.toLowerCase().trim();
    if (!search) return props.options;

    return props.options.filter(option => {
        const mainLabel = String(option[props.labelKey] ?? '').toLowerCase();
        const subLabel = props.subLabelKey ? String(option[props.subLabelKey] ?? '').toLowerCase() : '';
        return mainLabel.includes(search) || subLabel.includes(search);
    });
});

// Check if an option is selected (handles string/number type mismatches safely)
const isSelected = (option) => {
    const val = option[props.valueKey];
    return props.modelValue.some(item => String(item) === String(val));
};

// Map selected IDs back to full option objects for pills/badges
const selectedItems = computed(() => {
    return props.options.filter(option => isSelected(option));
});

// Toggle selection with type-safe matching
const toggleOption = (option) => {
    const val = option[props.valueKey];
    const newValue = [...props.modelValue];
    
    const index = newValue.findIndex(item => String(item) === String(val));

    if (index > -1) {
        newValue.splice(index, 1);
    } else {
        newValue.push(val);
    }

    emit('update:modelValue', newValue);
};

// Remove individual item via pill close button
const removeItem = (option) => {
    const val = option[props.valueKey];
    const newValue = props.modelValue.filter(item => String(item) !== String(val));
    emit('update:modelValue', newValue);
};

// Click outside to close dropdown
const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false;
    }
};

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>

<template>
    <div class="relative" ref="dropdownRef">
        <!-- Main Select Box Container -->
        <div 
            @click="isOpen = true"
            class="w-full min-h-[48px] rounded-xl border border-neutral-700 bg-neutral-950 px-3 py-2 text-sm text-neutral-200 outline-none transition cursor-pointer flex flex-wrap items-center gap-1.5 focus-within:border-amber-500 focus-within:ring-2 focus-within:ring-amber-500/10">
            
            <!-- Selected Pills (Shown inside the select box) -->
            <span 
                v-for="item in selectedItems" 
                :key="item[valueKey]"
                class="inline-flex items-center gap-1.5 rounded-lg border border-amber-500/20 bg-amber-500/10 px-2 py-1 text-xs text-amber-300">
                {{ item[labelKey] }}
                <button 
                    type="button" 
                    @click.stop="removeItem(item)"
                    class="text-amber-400 hover:text-white transition"
                    :aria-label="`Remove ${item[labelKey]}`">
                    &times;
                </button>
            </span>

            <!-- Search / Trigger Input -->
            <input 
                v-model="searchQuery" 
                type="text"
                :placeholder="selectedItems.length === 0 ? placeholder : ''"
                @focus="isOpen = true"
                @keydown.esc="isOpen = false"
                class="flex-1 bg-transparent min-w-[120px] outline-none text-neutral-200 placeholder-neutral-500 text-sm px-1 py-1" 
            />
        </div>

        <!-- Dropdown Options List -->
        <div v-if="isOpen"
            class="absolute z-30 mt-1 max-h-60 w-full overflow-y-auto rounded-xl border border-neutral-700 bg-neutral-950 p-1.5 shadow-2xl">
            
            <button 
                v-for="option in filteredOptions" 
                :key="option[valueKey]"
                type="button" 
                @click="toggleOption(option)"
                class="flex w-full items-start gap-3 rounded-lg px-3 py-2.5 text-left hover:bg-neutral-900 transition-colors">
                
                <!-- Custom Checkbox -->
                <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded border transition-colors"
                    :class="isSelected(option)
                        ? 'border-amber-500 bg-amber-500 text-black'
                        : 'border-neutral-700 bg-neutral-900'">
                    <svg v-if="isSelected(option)"
                        class="h-3 w-3" viewBox="0 0 20 20" fill="none"
                        stroke="currentColor" stroke-width="2.5">
                        <path d="m4 10 4 4 8-8" />
                    </svg>
                </span>

                <!-- Option Text -->
                <span class="min-w-0">
                    <span class="block text-xs font-medium text-neutral-200">
                        {{ option[labelKey] }}
                    </span>
                    <span v-if="subLabelKey && option[subLabelKey]" class="mt-0.5 block text-xs text-neutral-400">
                        {{ option[subLabelKey] }}
                    </span>
                </span>
            </button>

            <p v-if="filteredOptions.length === 0"
                class="px-3 py-4 text-center text-xs text-neutral-500">
                No matching results found.
            </p>
        </div>
    </div>
</template>