<template>
  <div class="space-y-4">
    <h3 class="text-lg font-bold text-gray-700">
      {{ translations.UpdateMeterReading }}
    </h3>

    <div class="space-y-4 text-sm">
      <div>
        <p class="font-semibold text-lg text-gray-700">
          {{ translations.PayeeName }}
        </p>
        <p class="text-lg text-gray-900 font-medium">
          {{ meter ? meter.PayeeName : "—" }}
        </p>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div class="text-left">
          <p class="font-semibold text-sm text-gray-700">
            {{ translations.LastMonthDue }}
          </p>
          <p class="text-gray-900 font-medium text-base">
            {{ meter ? meter.LastMonthDue : "—" }}
          </p>
        </div>

        <div class="text-right">
          <p class="font-semibold text-sm text-gray-700">
            {{ translations.Installment }}
          </p>
          <p class="text-gray-900 font-medium text-base">
            {{ meter ? meter.Installment : "—" }}
          </p>
        </div>

        <div class="text-left">
          <p class="font-semibold text-sm text-gray-700">
            {{ translations.AvgUnit }}
          </p>
          <p class="text-gray-900 font-medium text-base">
            {{ meter ? meter.AvgUnit : "—" }}
          </p>
        </div>

        <div class="text-right">
          <p class="font-semibold text-sm text-gray-700">
            {{ translations.PreReading }}
          </p>
          <p class="text-gray-900 font-medium text-base">
            {{ meter ? meter.PreReading : "—" }}
          </p>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <p class="font-semibold text-lg text-gray-700">
            {{ translations.UsedUnits }}
          </p>
          <p
            class="text-lg font-medium"
            :class="calculatedUsedUnits < 0 ? 'text-red-600' : 'text-gray-900'"
          >
            {{ calculatedUsedUnits }}
          </p>
        </div>

        <div class="text-right">
          <label class="block font-semibold text-lg text-gray-700 mb-1">
            {{ translations.NewReading }}
          </label>
          <input
            type="number"
            v-model.number="nowReading"
            class="w-full border px-3 py-2 rounded-lg text-lg"
            :disabled="!meter"
          />
        </div>
      </div>
    </div>

    <button
      class="w-full bg-gradient-to-r from-teal-300 via-cyan-300 to-sky-300 text-white py-2 rounded-lg hover:bg-green-700 disabled:bg-gray-300"
      @click="submitReading"
      :disabled="loading || !meter || calculatedUsedUnits < 0"
    >
      <span v-if="loading">Generating Bill...</span>
      <span v-else>Generate Bill</span>
    </button>

    <p v-if="calculatedUsedUnits < 0" class="text-red-600 text-sm">
      Used units cannot be negative
    </p>

    <p v-if="error" class="text-red-600 text-sm">{{ error }}</p>
    <p v-if="success" class="text-green-600 text-sm">{{ success }}</p>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "MeterUpdate",
  props: {
    meter: { type: Object, required: true },
    roleId: { type: String, required: true },
    routeId: { type: String, required: true },
    translations: { type: Object },
  },
  data() {
    return {
      nowReading: "",
      loading: false,
      error: null,
      success: null,
    };
  },
  computed: {
    calculatedUsedUnits() {
      if (!this.meter || this.nowReading === "" || this.nowReading == null)
        return this.meter ? this.meter.UsedUnits : "—";
      return this.nowReading - this.meter.PreReading;
    },
  },
  methods: {
    async submitReading() {
      if (this.nowReading === "" || this.nowReading == null) {
        this.error = "Please enter a new reading";
        return;
      }

      if (this.calculatedUsedUnits < 0) {
        this.error = "Used units cannot be negative";
        return;
      }

      this.loading = true;
      this.error = null;
      this.success = null;

      try {
        const body = {
          role_id: this.roleId,
          route_id: this.routeId,
          bcode: this.meter.BCode,
          pre_reading: this.meter.PreReading,
          now_reading: this.nowReading,
          used_units: this.calculatedUsedUnits,
        };

        const response = await axios.post("/api/meter/update", body);

        if (response.data.Status === 200) {
          if (response.data.PDF) {
            this.printWithLoopedLabs(response.data.PDF);
          }

          this.success = "Bill generated successfully";

          if (response.data.Next_BCode && response.data.Next_BCode !== 0) {
            this.$emit("next-meter", response.data.Next_BCode);
          }
        } else {
          this.error = response.data.Message || "Failed to generate bill";
        }
      } catch (e) {
        console.log("error at submit: ", e);
        this.error = "Service unavailable";
      } finally {
        this.loading = false;
        this.nowReading = "";
      }
    },

    printWithLoopedLabs(base64Pdf) {
      const url = `loopedlabs://print?payload=${encodeURIComponent(base64Pdf)}`;
      window.location.href = url;
    },
  },
};
</script>
