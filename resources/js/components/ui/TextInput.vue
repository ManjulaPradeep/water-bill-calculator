<template>
  <div class="mb-5">
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-500 font-bold">*</span>
    </label>

    <input
      :id="id"
      :type="type"
      :placeholder="placeholder"
      :readonly="readonly"
      :required="required"
      class="block w-full rounded-md shadow-sm sm:text-sm p-2 border border-gray-300
             focus:border-gray-400 focus:ring-gray-400 focus:outline-none focus:ring-1"
      v-model="model"
    />

    <ErrorMessage v-if="error" :message="error" />
  </div>
</template>

<script>
import ErrorMessage from "./ErrorMessage.vue";

export default {
  name: "TextInput",
  components: { ErrorMessage },
  props: {
    id: { type: String, default: '' },
    label: String,
    type: { type: String, default: "text" },
    placeholder: { type: String, default: "" },
    required: { type: Boolean, default: false },
    readonly: { type: Boolean, default: false },
    modelValue: String,
    error: String
  },
  computed: {
    model: {
      get() { return this.modelValue },
      set(value) { this.$emit("update:modelValue", value) }
    }
  }
};
</script>
