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
                        :class="
                            calculatedUsedUnits < 0
                                ? 'text-red-600'
                                : 'text-gray-900'
                        "
                    >
                        {{ calculatedUsedUnits }}
                    </p>
                </div>

                <div class="text-right">
                    <label
                        class="block font-semibold text-lg text-gray-700 mb-1"
                    >
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
            if (
                !this.meter ||
                this.nowReading === "" ||
                this.nowReading == null
            )
                return this.meter ? this.meter.UsedUnits : "—";
            return this.nowReading - this.meter.PreReading;
        },
    },
    methods: {
        async submitReading() {
            if (!this.nowReading) {
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
                    this.printBill(response.data);

                    this.success = "Bill generated successfully";

                    if (
                        response.data.Next_BCode &&
                        response.data.Next_BCode !== 0
                    ) {
                        this.$emit("next-meter", response.data.Next_BCode);
                    }
                } else {
                    this.error =
                        response.data.Message || "Failed to generate bill";
                }
            } catch (e) {
                console.log("error at submit:", e);
                this.error = "Service unavailable";
            }

            this.loading = false;
            this.nowReading = "";
        },

        // printBill(data) {
        //     let dynHtml = `print://escpos.org/escpos/bt/print?srcTp=uri&srcObj=html&src='data:text/html,`;

        //     // HEADER
        //     dynHtml += `<h3 style='text-align:center; font-size:14px; line-height:1.2;'>`;
        //     dynHtml += `කුඩා නගර ජලසම්පාදන<br/>`;
        //     dynHtml += `ක්‍රමය<br/>`;
        //     dynHtml += `මානව සංවර්ධන සංසදය, පල්ලෙබැද්ද<br/>`;
        //     dynHtml += `</h3>`;

        //     dynHtml += `<p style='text-align:center; margin-top:3px;'>0452241148 / 0775881173</p>`;
        //     dynHtml += `<p style='text-align:center; margin-top:1px;'>මාසික බිල්පත</p>`;
        //     dynHtml += `<hr/>`;

        //     // DATE / BILL NO
        //     dynHtml += `<p><span style='font-weight:bold;'>${data.ReadingDate}</span></p>`;
        //     dynHtml += `<p><span style='font-weight:bold;'>${data.InvoiceID}</span></p>`;
        //     dynHtml += `<hr/>`;

        //     // CUSTOMER DETAILS
        //     dynHtml += `<p><span>පාරිභෝගික අංකය</span> — <span style='font-weight:bold;'>${data.CustomerCode}</span></p>`;
        //     dynHtml += `<p style='margin-bottom:6px;'><span>පාරිභෝගික නම</span> — <span style='font-weight:bold;'>${data.CustomerName}</span></p>`;
        //     dynHtml += `<hr/>`;

        //     // AVERAGE
        //     dynHtml += `<p style='font-weight:bold;'><span>Avg. Unit</span> — <span>${data.AvgUnit}.00</span></p>`;
        //     dynHtml += `<hr/>`;

        //     // READINGS TABLE
        //     dynHtml += `<p>නව මනු කියවීම <span style='float:right; font-weight:bold;'>${data.NowReading}</span></p>`;
        //     dynHtml += `<p>පෙර මනු කියවීම <span style='float:right; font-weight:bold; text-decoration:underline;'>${data.PreReading}</span></p>`;
        //     dynHtml += `<p>ජල ඒකක <span style='float:right; font-weight:bold; text-decoration:underline;'>${data.TotalUnits}</span></p>`;

        //     // AMOUNTS TABLE
        //     dynHtml += `<p>ස්ථාවර ගාස්තුව <span style='float:right; font-weight:bold;'>${data.FixedAmount}.00</span></p>`;
        //     dynHtml += `<p>ඒකක ගාස්තුව <span style='float:right; font-weight:bold;'>${data.UnitAmount}.00</span></p>`;
        //     dynHtml += `<p>වෙනත් - / + <span style='float:right; font-weight:bold; text-decoration:underline;'>${data.CrDR}.00</span></p>`;
        //     dynHtml += `<p style='font-weight:bold;'>බිල්පත් ගාස්තුව <span style='float:right; font-weight:bold; text-decoration:underline;'>${data.TotalAmount}.00</span></p>`;

        //     // BALANCE
        //     dynHtml += `<p style='font-weight:bold;'>ශේෂය ඉ/ගෙ <span style='float:right;'>${data.LastMonthDue}.00</span></p>`;
        //     dynHtml += `<p>ණය වාරික <span style='float:right; font-weight:bold; text-decoration:underline;'>${data.Installment}.00</span></p>`;

        //     // TOTAL PAYABLE
        //     dynHtml += `<h3 style='font-size:14px; margin-top:4px; font-weight:bold;'>`;
        //     dynHtml += `ගෙවිය යුතු මුදල`;
        //     dynHtml += `<span style='float:right; text-decoration:underline; border-bottom:2px solid #000;'>${data.TotalDue}.00</span>`;
        //     dynHtml += `</h3>`;

        //     dynHtml += `<hr/>`;

        //     // FOOTER
        //     dynHtml += `<h3 style='text-align:center; margin-top:4px; font-weight:bold;'>ස්තුතියි !</h3>`;
        //     dynHtml += `<p style='text-align:center;'>© eTechnoLab • 0770 647 647</p>`;

        //     dynHtml += `'`;

        //     console.log('final print:', dynHtml);
        //     window.location.href = dynHtml;
        // },

        // printBill(data) {
        //     let html = `

        // <h3 style='text-align:center; margin:0; padding:0;'>
        //     කුඩා නගර ජලසම්පාදන<br/>
        //     ක්‍රමය<br/>
        //     මානව සංවර්ධන සංසදය, පල්ලෙබැද්ද
        // </h3>

        // <p style='text-align:center; margin:2px 0 4px 0;'>0452241148 / 0775881173</p>
        // <p style='text-align:center; margin:2px 0;'>මාසික බිල්පත</p>
        // <hr style='margin:4px 0;'/>

        // <p style='margin:2px 0;'>දිනය : <span style='float:right;'>${data.ReadingDate}</span></p>
        // <p style='margin:2px 0;'>බිල් අංකය : <span style='float:right;'>${data.InvoiceID}</span></p>
        // <hr style='margin:4px 0;'/>

        // <p style='margin:2px 0;'>පාරිභෝගික අංකය <span style='float:right;'>${data.CustomerCode}</span></p>
        // <p style='margin:2px 0 6px 0;'>නම <span style='float:right;'>${data.CustomerName}</span></p>
        // <hr style='margin:4px 0;'/>

        // <p style='margin:2px 0;'>Avg. Unit <span style='float:right;'>${data.AvgUnit}.00</span></p>
        // <hr style='margin:4px 0;'/>

        // <p style='margin:2px 0;'>නව මනු කියවීම
        //     <span style='float:right;'>${data.NowReading}</span>
        // </p>

        // <p style='margin:2px 0;'>පෙර මනු කියවීම
        //     <span style='float:right; display:inline-block; width:60px; text-align:right; border-bottom:1px solid #000;'>${data.PreReading}</span>
        // </p>

        // <p style='margin:2px 0 10px 0;'>ජල ඒකක
        //     <span style='float:right; display:inline-block; width:60px; text-align:right; border-bottom:1px solid #000;'>${data.TotalUnits}</span>
        // </p>

        // <p style='margin:2px 0;'>ස්ථාවර ගාස්තුව
        //     <span style='float:right;'>${data.FixedAmount}.00</span>
        // </p>

        // <p style='margin:2px 0;'>ඒකක ගාස්තුව
        //     <span style='float:right;'>${data.UnitAmount}.00</span>
        // </p>

        // <p style='margin:2px 0;'>වෙනත් - / +
        //     <span style='float:right; display:inline-block; width:60px; text-align:right; border-bottom:1px solid #000;'>${data.CrDR}.00</span>
        // </p>

        // <p style='margin:2px 0 10px 0;'>බිල්පත් ගාස්තුව
        //     <span style='float:right; display:inline-block; width:60px; text-align:right; border-bottom:1px solid #000;'>${data.TotalAmount}.00</span>
        // </p>

        // <p style='margin:2px 0;'>ශේෂය ඉ/ගෙ
        //     <span style='float:right;'>${data.LastMonthDue}.00</span>
        // </p>

        // <p style='margin:2px 0;'>ණය වාරික
        //     <span style='float:right; display:inline-block; width:80px; text-align:right; border-bottom:1px solid #000;'>${data.Installment}.00</span>
        // </p>

        // <h3 style='margin:2px 0 10px 0; font-size:13px;'>
        //     ගෙවිය යුතු මුදල
        //     <span style='float:right; display:inline-block; width:80px; text-align:right; border-bottom:2px solid #000;'>${data.TotalDue}.00</span>
        // </h3>

        // <hr style='margin:6px 0;'/>

        // <p style='text-align:center; margin:4px 0;'>ස්තුතියි !</p>
        // <p style='text-align:center; margin:4px 0;'>© eTechnoLab • 0770 647 647</p> `;

        //     console.log("HTML for print:\n\n", html);

        //     const dynHtml = `print://escpos.org/escpos/bt/print?srcTp=uri&srcObj=html&src='data:text/html,${html}'`;

        //     window.location.href = dynHtml;
        // },




printBill(data) {
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
            dynHtml += `<p style='margin:2px 0;'>දිනය : <span style='float:right;'>${data.ReadingDate} </span></p>`;
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
            dynHtml += `<p style=' line-height:0.5;'>ස්ථාවර ගාස්තුව <span style='float:right;font-size:22px;'>${data.FixedAmount}.00</span></p>`;
            dynHtml += `<p style=' line-height:0.5;'>ඒකක ගාස්තුව <span style='float:right;font-size:22px;'>${data.UnitAmount}.00</span></p>`;
            dynHtml += `<p style=' line-height:0.5;'>වෙනත් - / + <span style='float:right;font-size:22px;'>${data.CrDR}.00</span></p>`;
            dynHtml += `<hr style='margin:2px 0;' />`;
			dynHtml += `<p style=' line-height:1.2;'>බිල්පත් ගාස්තුව <span style='float:right;font-size:22px;'>${data.TotalAmount}.00</span></p>`;

            // BALANCE
            dynHtml += `<p style=' line-height:1.2;'>ශේෂය ඉ/ගෙ <span style='float:right;font-size:22px;'>${data.LastMonthDue}.00</span></p>`;
			dynHtml += `<p style=' line-height:1.2;'>ණය වාරික <span style='float:right;font-size:22px;'>${data.Installment}.00</span></p>`;
            dynHtml += `<hr style='margin:2px 0;' />`;

			// TOTAL PAYABLE
			dynHtml += `<p style=' line-height:1.2;'>ගෙවිය යුතු මුදල <span style='float:right;font-size:22px;'>${data.TotalDue}.00</span></p>`;
			//dynHtml += `<p style='margin:2px 0;'>ගෙවිය යුතු මුදල <span style='float:right;font-size:18px;'>${data.TotalDue}.00</span></p>`;

			dynHtml += `</p>`;

            dynHtml += `<div style='clear:both;'></div>`;

            dynHtml += `<hr/>`;

            // FOOTER
            dynHtml += `<h3 style='text-align:center; margin-top:4px; line-height:1.2;'>ස්තුතියි !</h3>`;
            dynHtml += `<p style='text-align:center; line-height:1.2;'>© eTechnoLabs • 0770 647 647</p>`;

            dynHtml += `'`; //<br/><br/>

            console.log("final print:", dynHtml);
            window.location.href = dynHtml;
        },

        generateDynamicHTML() {
            let dynHtml = `print://escpos.org/escpos/bt/print?srcTp=uri&srcObj=html&src='data:text/html,`;
            // dynHtml += `<h1 style='text-align:center'>PRINTING DYNAMICALLY GENERATED HTML</h1>'`;
            dynHtml += `<h1 style='text-align:center'>PRINTING DYNAMICALLY GENERATED HTML</h1>`;
            dynHtml += `<h3 style="text-align:center;">කුඩා නගර ජලසම්පාදන</h3>`;
            dynHtml += `<h4 style="text-align:center;">ක්‍රමය</h4>`;
            dynHtml += `<h4 style="text-align:center;">මානව සංවර්ධන සංසදය, පල්ලෙබැද්ද</h4>`;
            dynHtml += `<p style="text-align:center;">0452241148 / 0775881173</p>`;
            dynHtml += `<p style="text-align:center;">0452241148 / 0775881173</p>`;
            dynHtml += `'`;
            window.location.href = dynHtml;
        },
    },
};
</script>
