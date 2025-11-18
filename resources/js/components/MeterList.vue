<template>
    <div class="bg-white p-4 rounded shadow mb-6 max-w-4xl mx-auto">
        <h2 class="text-xl font-bold mb-2">Select Meter</h2>

        <div v-if="loading">Loading meters...</div>
        <div v-if="error" class="text-red-600">{{ error }}</div>

        <select v-if="meters.length" v-model="selectedBCode" @change="onSelect" class="w-full border p-2 rounded">
            <option value="">-- Select Meter --</option>
            <option v-for="meter in meters" :key="meter.BCode" :value="meter.BCode">
                {{ meter.BCode }} — {{ meter.PayeeName }}
            </option>
        </select>
    </div>
</template>

<script>
import axios from "axios";

export default {
    name: "MeterList",
        props: {
        roleId: { type: String, required: true },
        routeId: { type: String, required: true },
    },
    data() {
        return {
            meters: [],
            loading: false,
            error: null,
            selectedBCode: ""
        };
    },
    async mounted() {
        this.fetchMeters();
    },
    
    methods: {

        async fetchMeters() {
            this.loading = true;
            this.error = null;

            try {
                const body = {
                    role_id: this.roleId,
                    route_id: this.routeId
                };

                const response = await axios.post('/api/meter/list', body);

                if (response.status == 200) {
                    this.meters = response.data;
                    console.log('this.meters: ', this.meters);
                } else {
                    this.error = response.data.Message || 'Failed to load meters';
                }
            } catch (err) {
                console.log('error at fetchMeters: ', err);
                this.error = 'Service unavailable';
            } finally {
                this.loading = false;
            }
        },

        onSelect() {
            const selected = this.meters.find(m => m.BCode === this.selectedBCode);
            this.$emit("selected", selected);
        }
    }
};
</script>
