<template>
  <p class="mt-1 text-sm text-red-600">{{ message }}</p>
</template>

<script>
export default {
  name: "ErrorMessage",
  props: {
    message: { type: String, required: true }
  }
};
</script>

