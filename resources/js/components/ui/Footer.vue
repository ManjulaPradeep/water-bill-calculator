<template>
  <div>
    <footer class="text-center text-gray-500 text-sm mt-6">
      &copy; {{ currentYear }} TechnoLabs (Pvt) Ltd. All rights reserved.
    </footer>
  </div>
</template>

<script>
export default {
  name: "AppFooter",
  data() {
    return {
      currentYear: new Date().getFullYear()
    };
  }
};
</script>

<style scoped>
/* Optional styles */
</style>
