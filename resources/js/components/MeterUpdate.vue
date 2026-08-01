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
                    <p class="text-lg font-medium" :class="calculatedUsedUnits < 0
                            ? 'text-red-600'
                            : 'text-gray-900'
                        ">
                        {{ calculatedUsedUnits }}
                    </p>
                </div>

                <div class="text-right">
                    <label class="block font-semibold text-lg text-gray-700 mb-1">
                        {{ translations.NewReading }}
                    </label>
                    <input type="number" v-model.number="nowReading" class="w-full border px-3 py-2 rounded-lg text-lg"
                        :disabled="!meter" />
                </div>
            </div>
        </div>

        <!-- GPS Blocked Overlay -->
        <div v-if="gpsBlocked" class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
            <svg class="mx-auto h-8 w-8 text-red-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <p class="text-red-700 font-medium text-sm">{{ gpsErrorMessage }}</p>

            <button class="mt-3 px-4 py-2 bg-blue-600 text-white rounded" @click="retryGps">
                Retry GPS
            </button>
        </div>

        <button
            class="w-full bg-gradient-to-r from-teal-300 via-cyan-300 to-sky-300 text-white py-2 rounded-lg hover:bg-green-700 disabled:bg-gray-300"
            @click="submitReading" :disabled="loading || !meter || calculatedUsedUnits < 0">
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
        meter: {
            type: Object,
            required: true,
        },

        roleId: {
            type: String,
            required: true,
        },

        routeId: {
            type: String,
            required: true,
        },

        translations: {
            type: Object,
        },
    },


    data() {
        return {
            nowReading: "",
            loading: false,
            error: null,
            success: null,
            gpsLat: null,
            gpsLng: null,
            gpsAccuracy: null,
            gpsBlocked: false,
            gpsErrorMessage: "",
        };
    },


    computed: {

        calculatedUsedUnits() {
            if (
                !this.meter ||
                this.nowReading === "" ||
                this.nowReading == null
            ) {
                return this.meter
                    ? this.meter.UsedUnits
                    : "—";
            }
            return this.nowReading - this.meter.PreReading;
        }

    },


    mounted() { },

    methods: {

        async retryGps() {
            this.gpsBlocked = false;
            this.gpsErrorMessage = "";
            this.error = "";
        },

        getGpsLocation() {
            return new Promise((resolve, reject) => {

                if (!navigator.geolocation) {
                    reject({
                        code: 0,
                        message: "Geolocation not supported"
                    });
                    return;
                }

                navigator.geolocation.getCurrentPosition(

                    (position) => {
                        this.gpsLat = position.coords.latitude;
                        this.gpsLng = position.coords.longitude;
                        this.gpsAccuracy = position.coords.accuracy;
                        this.gpsBlocked = false;
                        this.gpsErrorMessage = "";
                        resolve(position);
                    },

                    (error) => { reject(error); },

                    {
                        enableHighAccuracy: true,
                        timeout: 15000,
                        maximumAge: 30000
                    }
                );
            });
        },

        async submitReading() {
            this.error = null;
            this.success = null;

            if (
                this.nowReading === "" ||
                this.nowReading == null ||
                Number.isNaN(this.nowReading)
            ) {

                this.error = "Please enter a new reading";
                return;
            }

            if (this.calculatedUsedUnits < 0) {
                this.error = "Used units cannot be negative";
                return;
            }

            this.loading = true;

            // Get fresh GPS when submitting
            try {
                await this.getGpsLocation();
            }
            catch (error) {
                this.gpsBlocked = true;

                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        this.gpsErrorMessage =
                            "Location permission denied.\n\nPlease click the location icon in your browser address bar and change the permission to Allow.";
                        break;

                    case error.POSITION_UNAVAILABLE:
                        this.gpsErrorMessage =
                            "GPS signal unavailable. Please move outdoors or enable GPS.";
                        break;

                    case error.TIMEOUT:
                        this.gpsErrorMessage =
                            "GPS request timed out. Please try again.";
                        break;

                    default:
                        this.gpsErrorMessage = "Unable to obtain GPS location.";
                }

                this.loading = false;
                return;
            }

            if (this.gpsLat === null || this.gpsLng === null) {
                this.error = "GPS location is required.";
                this.loading = false;
                return;
            }

            try {

                const body = {
                    role_id: this.roleId,
                    route_id: this.routeId,
                    bcode: this.meter.BCode,
                    pre_reading: this.meter.PreReading,
                    now_reading: this.nowReading,
                    used_units: this.calculatedUsedUnits,
                    gps_lat: this.gpsLat,
                    gps_lng: this.gpsLng,
                    gps_accuracy: this.gpsAccuracy
                };

                const response = await axios.post("/api/meter/update",
                    body
                );

                if (response.data.Status === 200) {
                    console.log("Bill generated successfully:", response.data);
                    this.printBill(response.data);

                    this.success = "Bill generated successfully";

                    if (
                        response.data.Next_BCode &&
                        response.data.Next_BCode !== 0
                    ) {

                        this.$emit(
                            "next-meter",
                            response.data.Next_BCode
                        );
                    }
                }
                else {
                    this.error =
                        response.data.Message ||
                        "Failed to generate bill";
                }
            }
            catch (error) {
                console.log("Submit error:", error);
                this.error = "Service unavailable";
            }

            this.loading = false;
            this.nowReading = "";
        },

        printBill(data) {
            const formatAmount = (value) => {
                const numericValue = Number.parseFloat(value);
                return Number.isFinite(numericValue) ? numericValue.toFixed(2) : "0.00";
            };

            let dynHtml = `print://escpos.org/escpos/bt/print?srcTp=uri&srcObj=html&src='data:text/html,`;

            // HEADER
            dynHtml += `<h4 style='text-align:center;  margin:0; padding:0;'>`;
            dynHtml += `කුඩා නගර ජලසම්පාදන<br/>`;
            dynHtml += `ක්‍රමය<br/>`;
            dynHtml += `මානව සංවර්ධන සංසදය, පල්ලෙබැද්ද<br/>`;
            dynHtml += `</h3>`;

            dynHtml += `<p style='text-align:center; margin:2px 0 4px 0;'>0452241148 / 0775881173</p>`;
            dynHtml += `<p style='text-align:center; margin:2px 0;'>මාසික බිල්පත</p`;
            dynHtml += `<hr style='margin:4px ;' />`;

            // DATE / BILL NO
            dynHtml += `<p style='text-align:center; margin:2px 0; font-size:14px;'>${data.BillCategory} | ${data.RouteID} | ${data.ReadingDate}</p>`;
            dynHtml += `<p style='margin:2px 0;'>බිල් අංකය : <span style='float:right;font-size:18px;'>${data.InvoiceID}</span></p>`;
            dynHtml += `<hr style='margin:4px 0;' />`;

            // CUSTOMER DETAILS
            dynHtml += `<p style='margin:2px 0;'>පාරිභෝගික අංකය/නම</p>`;
            dynHtml += `<p style='text-align:center; margin:4px 0;font-size:18px;'>${data.CustomerCode}</p>`;
            dynHtml += `<p style='text-align:left; margin:4px 0;font-size:16px;'>${data.CustomerName}</p>`;
            dynHtml += `<hr style='margin:4px 0;' />`;

            // AVERAGE
            dynHtml += `<p style='margin:2px 0;'>Avg.Unit <span style='float:right;font-size:18px;'>${data.AvgUnit}.00</span></p>`;
            dynHtml += `<hr style='margin:4px 0;' />`;

            // READINGS TABLE
            dynHtml += `<p style=' line-height:0.5;'>නව මනු කියවීම <span style='float:right;font-size:18px;'>${data.NowReading}</span></p>`;
            dynHtml += `<p style=' line-height:0.5;'>පෙර මනු කියවීම <span style='float:right;font-size:18px;'>${data.PreReading}</span></p>`;
            dynHtml += `<hr style='margin:2px 0;line-height:0.5;' />`;
            dynHtml += `<p style=' line-height:0.5;'>ජල ඒකක <span style='float:right;font-size:22px;'>${data.TotalUnits}</span></p>`;
            dynHtml += `<hr style='margin:2px 0;' />`;

            // AMOUNTS TABLE
            dynHtml += `<p style=' line-height:0.5;'>සේවා ගාස්තුව <span style='float:right;font-size:22px;'>${formatAmount(data.FixedAmount)}</span></p>`;
            dynHtml += `<p style=' line-height:0.5;'>ඒකක ගාස්තුව <span style='float:right;font-size:22px;'>${formatAmount(data.UnitAmount)}</span></p>`;
            dynHtml += `<p style=' line-height:0.5;'>වෙනත් - / + <span style='float:right;font-size:22px;'>${formatAmount(data.CrDR)}</span></p>`;
            dynHtml += `<hr style='margin:2px 0;' />`;
            dynHtml += `<p style=' line-height:1.2;'>බිල්පත් ගාස්තුව <span style='float:right;font-size:22px;'>${formatAmount(data.TotalAmount)}</span></p>`;

            // BALANCE
            dynHtml += `<p style=' line-height:1.2;'>ශේෂය ඉ/ගෙ <span style='float:right;font-size:22px;'>${formatAmount(data.LastMonthDue)}</span></p>`;
            dynHtml += `<p style=' line-height:1.2;'>ණය වාරික <span style='float:right;font-size:22px;'>${formatAmount(data.Installment)}</span></p>`;
            dynHtml += `<hr style='margin:2px 0;' />`;

            // TOTAL PAYABLE
            dynHtml += `<p style=' line-height:1.2;'>ගෙවිය යුතු මුදල <span style='float:right;font-size:22px;'>${formatAmount(data.TotalDue)}</span></p>`;

            dynHtml += `</p>`;

            dynHtml += `<div style='clear:both;'></div>`;

            dynHtml += `<hr/>`;

            dynHtml += `<p style='text-align:center; margin:2px 0; font-size:20px;'>${data.BankAccount}</p>`;
            dynHtml += `<p style='text-align:center; margin:2px 0; font-size:15px;'>${data.BankName}</p>`;
            dynHtml += `<hr style='margin:4px 0;' />`;

            // FOOTER
            dynHtml += `<h3 style='text-align:center; margin-top:4px; line-height:1.2;'>ස්තුතියි !</h3>`;
            dynHtml += `<p style='text-align:center; line-height:1.2;'>© eTechnoLabs • 0770 647 647</p>`;

            dynHtml += `'`;

            console.log("final print:", dynHtml);
            window.location.href = dynHtml;
        },

    },
};
</script>
