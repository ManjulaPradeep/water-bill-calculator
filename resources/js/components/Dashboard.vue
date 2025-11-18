<template>
    <div
        class="min-h-screen w-full bg-transparent p-3 sm:p-6 lg:p-8 flex justify-center items-start"
    >
        <div
            class="w-full max-w-lg bg-white/70 backdrop-blur-xl rounded-3xl shadow-xl border border-white/40 overflow-hidden"
        >
            <div
                class="relative bg-gradient-to-r from-teal-300 via-cyan-300 to-sky-300 p-5 text-white"
            >
                <div
                    class="absolute inset-0 bg-white/20 rounded-3xl backdrop-blur-sm"
                ></div>

                <div class="relative flex justify-between items-center">
                    <h2
                        class="text-lg sm:text-xl font-bold tracking-wide drop-shadow"
                    >
                        Water Billing Portal
                    </h2>

                    <button
                        @click="showLogoutConfirm = true"
                        class="bg-white/30 backdrop-blur-md px-3 py-1.5 rounded-full shadow hover:bg-white/40 transition text-sm font-medium text-white"
                    >
                        Logout
                    </button>
                </div>

                <p class="relative mt-1.5 text-xs sm:text-sm text-white/90">
                    Easy, fast & accurate billing
                </p>

                <div
                    class="relative mt-4 rounded-xl text-white text-sm sm:text-base"
                >
                    <div class="flex flex-col space-y-1 text-left">
                        <div class="font-medium">
                            <span class="opacity-80">මාසය: </span>
                            <span>{{ currentMonth }}</span>
                        </div>

                        <div class="font-medium">
                            <span class="opacity-80">මාර්ග අංකය: </span>
                            <span>{{ routeId }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-5 space-y-6">
                <meter-list
                    ref="meterList"
                    :role-id="roleId"
                    :route-id="routeId"
                    @selected="onMeterSelected"
                />

                <meter-update
                    :meter="selectedMeter"
                    :role-id="roleId"
                    :route-id="routeId"
                    :translations="translations"
                    @next-meter="selectNextMeter"
                />
            </div>
        </div>

        <transition name="fade">
            <div
                v-if="showLogoutConfirm"
                class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            >
                <div
                    class="bg-white/90 backdrop-blur-xl p-6 rounded-2xl shadow-2xl w-full max-w-xs animate-scaleIn"
                >
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">
                        Confirm Logout
                    </h2>

                    <p class="text-gray-600 text-sm mb-6">
                        Are you sure you want to log out?
                    </p>

                    <div class="flex justify-between space-x-2">
                        <button
                            @click="showLogoutConfirm = false"
                            class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition text-sm font-medium"
                        >
                            Cancel
                        </button>

                        <button
                            @click="logout"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-medium"
                        >
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
import MeterList from "./MeterList.vue";
import MeterUpdate from "./MeterUpdate.vue";
import axios from "axios";

export default {
    name: "Dashboard",
    components: { MeterList, MeterUpdate },

    props: {
        roleId: { type: String, required: true },
        routeId: { type: String, required: true },
        translations: { type: Object },
    },

    data() {
        return {
            selectedMeter: null,
            showLogoutConfirm: false,
            currentMonth: "",
        };
    },

    mounted() {
        // Format month like: "January 2025"
        const date = new Date();
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, "0");

        // Format: YYYY-MM
        this.currentMonth = `${year}-${month}`;
    },

    methods: {
        onMeterSelected(meter) {
            this.selectedMeter = meter;
        },

        /**
         * FIXED: Next meter selection based on array index
         */
        selectNextMeter() {
            const meters = this.$refs.meterList.meters;
            const currentIndex = meters.findIndex(
                (m) => m.BCode === this.selectedMeter.BCode
            );

            if (currentIndex !== -1 && currentIndex < meters.length - 1) {
                const nextMeter = meters[currentIndex + 1];

                this.selectedMeter = nextMeter;
                this.$refs.meterList.selectedBCode = nextMeter.BCode;
            }
        },

        logout() {
            axios
                .post("/logout")
                .then(() => (window.location.href = "/"))
                .catch((err) => console.error("Logout failed", err));
        },
    },
};
</script>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

@keyframes scaleIn {
    0% {
        transform: scale(0.7);
        opacity: 0;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}
.animate-scaleIn {
    animation: scaleIn 0.25s ease-out;
}
</style>
