<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        ref="input"
        v-model="model"
        class="input mt-1"
        :class="{ 'border-red-300 focus:border-red-300 focus:ring-red-500/20': $attrs.class?.includes('border-red-300') }"
    />
</template>
