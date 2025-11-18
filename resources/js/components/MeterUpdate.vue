<template>
    <div class="space-y-4">
        <h3 class="text-lg font-bold text-gray-700">
            {{ translations.UpdateMeterReading }}
        </h3>

        <div class="space-y-3 text-sm">

            <div>
                <p class="text-gray-500">{{ translations.PayeeName }}</p>
                <p class="text-gray-900 font-medium">
                    {{ meter ? meter.PayeeName : '—' }}
                </p>
            </div>

            <div>
                <p class="text-gray-500">{{ translations.LastMonthDue }}</p>
                <p class="text-gray-900 font-medium">
                    {{ meter ? meter.LastMonthDue : '—' }}
                </p>
            </div>

            <div>
                <p class="text-gray-500">{{ translations.Installment }}</p>
                <p class="text-gray-900 font-medium">
                    {{ meter ? meter.Installment : '—' }}
                </p>
            </div>

            <div>
                <p class="text-gray-500">{{ translations.AvgUnit }}</p>
                <p class="text-gray-900 font-medium">
                    {{ meter ? meter.AvgUnit : '—' }}
                </p>
            </div>

            <div>
                <p class="text-gray-500">{{ translations.PreReading }}</p>
                <p class="text-gray-900 font-medium">
                    {{ meter ? meter.PreReading : '—' }}
                </p>
            </div>

            <div>
                <p class="text-gray-500">{{ translations.UsedUnits }}</p>
                <p class="text-gray-900 font-medium">
                    {{ meter ? meter.UsedUnits : '—' }}
                </p>
            </div>

        </div>

        <div>
            <label class="block font-medium text-gray-700 mb-1">
                {{ translations.NewReading }}
            </label>
            <input type="number" v-model="nowReading" class="w-full border px-3 py-2 rounded-lg" :disabled="!meter">
        </div>

        <button class="w-full bg-gradient-to-r from-teal-300 via-cyan-300 to-sky-300 text-white py-2 rounded-lg hover:bg-green-700 disabled:bg-gray-300"
            @click="submitReading" :disabled="loading || !meter">
            <span v-if="loading">Generating Bill...</span>
            <span v-else>Generate Bill</span>
        </button>

        <p v-if="error" class="text-red-600 text-sm">{{ error }}</p>
        <p v-if="success" class="text-green-600 text-sm">{{ success }}</p>

    </div>
</template>




<script>
import axios from 'axios';

export default {
    name: 'MeterUpdate',
    props: {
        meter: { type: Object, required: true },
        roleId: { type: String, required: true },
        routeId: { type: String, required: true },
        translations: { type: Object }
    },
    data() {
        return {
            nowReading: '',
            loading: false,
            error: null,
            success: null
        };
    },
    methods: {
        mounted() {
            console.log("MeterUpdate mounted:");
            console.log("MeterUpdate props:", this.meter);
        },

        showPDF(base64String) {
            const pdfUrl = "data:application/pdf;base64," + base64String;
            window.open(pdfUrl, "_blank");
        },

        printWithLoopedLabs(base64Pdf) {
            console.log('called printWithLoopedLabs function');
            const url = `loopedlabs://print?payload=${encodeURIComponent(base64Pdf)}`;
            window.location.href = url;
        },

        // async submitReading() {
        //     if (!this.nowReading) {
        //         this.error = "Please enter a new reading";
        //         return;
        //     }

        //     this.loading = true;
        //     this.error = null;
        //     this.success = null;

        //     try {
        //         const body = {
        //             role_id: this.roleId,
        //             route_id: this.routeId,
        //             bcode: this.meter.BCode,
        //             pre_reading: this.meter.PreReading,
        //             now_reading: this.nowReading
        //         };

        //         const response = await axios.post("/api/meter/update", body);

        //         if (response.data.Status === 200) {

        //             // DIRECT BLUETOOTH PRINT VIA LoopedLabs
        //             if (response.data.PDF) {
        //                 this.printWithLoopedLabs(response.data.PDF);
        //             }

        //             this.success = "Bill generated successfully";

        //         } else {
        //             this.error = response.data.Message || "Failed to generate bill";
        //         }

        //     } catch (e) {
        //         console.log('error at submit: ', e);
        //         this.error = "Service unavailable";
        //     } finally {
        //         this.loading = false;
        //     }
        // },



        async submitReading() {
            if (!this.nowReading) {
                this.error = "Please enter a new reading";
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
                    now_reading: this.nowReading
                };

                const response = await axios.post("/api/meter/update", body);

                if (response.data.Status === 200) {
                    // DIRECT BLUETOOTH PRINT VIA LoopedLabs
                    if (response.data.PDF) {
                        this.printWithLoopedLabs(response.data.PDF);
                    }

                    this.success = "Bill generated successfully";

                    // setTimeout(() => {
                    //     this.success = null;
                    // }, 3000);

                    // Emit next meter BCode to parent
                    if (response.data.Next_BCode && response.data.Next_BCode !== 0) {
                        this.$emit("next-meter", response.data.Next_BCode);
                    }

                } else {
                    this.error = response.data.Message || "Failed to generate bill";
                }

            } catch (e) {
                console.log('error at submit: ', e);
                this.error = "Service unavailable";
            } finally {
                this.loading = false;
                this.nowReading = "";
            }
        },



        printPDF(base64Data) {
            console.log('callled printPDF function');
            const byteChars = atob(base64Data);
            const byteNumbers = new Array(byteChars.length);
            for (let i = 0; i < byteChars.length; i++) byteNumbers[i] = byteChars.charCodeAt(i);
            const byteArray = new Uint8Array(byteNumbers);
            const blob = new Blob([byteArray], { type: 'application/pdf' });
            const blobUrl = URL.createObjectURL(blob);

            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.src = blobUrl;
            document.body.appendChild(iframe);

            iframe.onload = () => {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
                document.body.removeChild(iframe);
            };
        }
    }
};
</script>
