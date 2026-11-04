
<script setup>
import { ref, computed } from 'vue';
import countryList from 'country-list';
import {
    Combobox,
    ComboboxInput,
    ComboboxButton,
    ComboboxOptions,
    ComboboxOption,
    TransitionRoot,
} from '@headlessui/vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: 'GY',
    },
    error: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);

// Store uppercase ISO 3166-1 alpha-2 codes.
const countries = countryList.getData()
    .map(c => ({
        name: c.name,
        code: c.code.toUpperCase(),
        flagCode: c.code.toLowerCase(),
    }))
    .sort((a, b) => a.name.localeCompare(b.name));

const query = ref('');

const filteredCountries = computed(() =>
    query.value === ''
        ? countries
        : countries.filter(country =>
            country.name
                .toLowerCase()
                .replace(/\s+/g, '')
                .includes(query.value.toLowerCase().replace(/\s+/g, ''))
        )
);

// modelValue now represents a country code, e.g. "GY".
const selectedCountry = computed({
    get() {
        const value = props.modelValue?.trim();

        return countries.find(c =>
            c.code === value?.toUpperCase()
        ) || countries.find(c => c.code === 'GY') || countries[0];
    },
    set(country) {
        if (country) {
            emit('update:modelValue', country.code);
            query.value = '';
        }
    },
});
</script>

<template>
    <div class="w-full">
        <Combobox v-model="selectedCountry">
            <div class="relative">
                <div
                    class="relative w-full rounded-xl bg-[#0a0a0a] border border-neutral-800 focus-within:border-amber-500 transition-colors shadow-inner flex items-center px-3 py-1"
                >
                    <!-- Selected country flag -->
                    <span
                        v-if="selectedCountry"
                        :class="`fi fi-${selectedCountry.flagCode}`"
                        class="w-5 h-4 rounded-sm flex-shrink-0 mr-2.5 shadow-sm"
                    ></span>

                    <!-- Searchable country input -->
                    <ComboboxInput
                        class="w-full bg-transparent py-2.5 text-sm text-white placeholder-neutral-500 focus:outline-none border-none ring-0 focus:ring-0"
                        :displayValue="country => country?.name ?? ''"
                        @change="query = $event.target.value"
                        placeholder="Search country..."
                    />

                    <ComboboxButton
                        class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer"
                    >
                        <svg
                            class="w-4 h-4 text-neutral-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 9l-7 7-7-7"
                            />
                        </svg>
                    </ComboboxButton>
                </div>

                <TransitionRoot
                    leave="transition ease-in duration-100"
                    leaveFrom="opacity-100"
                    leaveTo="opacity-0"
                    @after-leave="query = ''"
                >
                    <ComboboxOptions
                        class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-xl bg-[#121212] border border-neutral-800 py-1 text-sm shadow-2xl ring-1 ring-black/5 focus:outline-none scrollbar-thin scrollbar-thumb-neutral-800"
                    >
                        <div
                            v-if="filteredCountries.length === 0 && query !== ''"
                            class="relative cursor-default select-none px-4 py-3 text-neutral-500 text-xs"
                        >
                            No country found.
                        </div>

                        <ComboboxOption
                            v-for="country in filteredCountries"
                            :key="country.code"
                            :value="country"
                            v-slot="{ selected, active }"
                            as="template"
                        >
                            <li
                                :class="[
                                    active
                                        ? 'bg-amber-500/15 text-amber-400 font-semibold'
                                        : 'text-neutral-300',
                                    'relative cursor-pointer select-none py-2.5 pl-3.5 pr-9 flex items-center gap-3 text-sm transition-colors'
                                ]"
                            >
                                <span
                                    :class="`fi fi-${country.flagCode}`"
                                    class="w-5 h-4 rounded-sm flex-shrink-0 shadow-sm"
                                ></span>

                                <span
                                    :class="[
                                        selected ? 'font-bold text-amber-400' : 'font-normal',
                                        'block truncate'
                                    ]"
                                >
                                    {{ country.name }}
                                </span>

                                <span
                                    v-if="selected"
                                    :class="[
                                        active ? 'text-amber-400' : 'text-amber-500',
                                        'absolute inset-y-0 right-0 flex items-center pr-3'
                                    ]"
                                >
                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2.5"
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>
                                </span>
                            </li>
                        </ComboboxOption>
                    </ComboboxOptions>
                </TransitionRoot>
            </div>
        </Combobox>

        <p v-if="error" class="mt-1 text-xs text-red-500 font-medium">
            {{ error }}
        </p>
    </div>
</template>
