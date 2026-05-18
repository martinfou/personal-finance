<script setup>
defineProps({
    value: String,
});
</script>

<template>
    <label class="label">
        {{ value }}
    </label>
</template>
