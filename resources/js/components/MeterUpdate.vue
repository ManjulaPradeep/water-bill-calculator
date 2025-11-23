<template>
    <div class="space-y-4">
        <h3 class="text-lg font-bold text-gray-700">
            {{ translations.UpdateMeterReading }}
        </h3>

        <!-- test -->
        <button
            class="w-full bg-blue-600 text-white py-2 rounded-lg"
            @click="generateDynamicHTML"
        >
            Test Printer
        </button>


        <a href="print://escpos.org/escpos/bt/print?srcTp=uri
   &srcObj=html
   &numCopies=1 //added in version 2.3.2
   &src='https://wb.hrms.lk/storage/bills/bill_001.html'">Print Me !</a>

   <br/><br/>

        <!-- test end -->

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
                    const html = this.generateBillHTML(response.data);
                    // this.printBill(html);
                    // this.printBillTest(html);
                    await this.saveAndPrint(html);

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

        // printBill(html) {
        //     const encoded = encodeURIComponent(html);
        //     window.location.href = `loopedlabs://print?html=${encoded}`;
        // },

        printBill(html) {
            // const encoded = encodeURIComponent(html);

            const printLink =
                "print://escpos.org/escpos/bt/print" +
                "?srcTp=uri" +
                "&srcObj=html" +
                "&numCopies=1" +
                "&src='data:text/html," +
                html +
                "'";

            window.location.href = printLink;
        },

        printBillTest(html) {
            let dynHtmlTest = `print://escpos.org/escpos/bt/print?srcTp=uri&srcObj=html&src='data:text/html,`;
            dynHtmlTest += html + "'";
            window.location.href = dynHtmlTest;
        },

        // test
        btPrintBillUrl(url) {
            console.log(" URL:", url);
            const encodedUrl = encodeURIComponent(url);
            const printUrl = `print://escpos.org/escpos/bt/print?srcTp=uri&srcObj=html&src='${encodedUrl}'`;
            console.log("Final print URL:", printUrl);
            window.location.href = printUrl;
        },

        // Function to save the bill and print it
        async saveAndPrint(html) {
            try {
                const filename = `bill_${this.routeId}.html`;
                const response = await axios.post("/api/save-bill", {
                    html: html,
                    filename: filename,
                });
                const fileUrl = response.data.url;
                this.btPrintBillUrl(fileUrl);
            } catch (error) {
                console.error("Error saving bill:", error);
                this.error = "Failed to save bill for printing";
            }
        },

        generateDynamicHTML() {
            let dynHtml = `print://escpos.org/escpos/bt/print?srcTp=uri&srcObj=html&src='data:text/html,`;
            dynHtml += `<h1 style='text-align:center'>PRINTING DYNAMICALLY GENERATED HTML</h1>'`;
            window.location.href = dynHtml;
        },


        generateBillHTML(data) {
    return `
<html>
  <head>
    <meta charset="UTF-8" />
  </head>
  <body style="width:110px; font-family:monospace; margin:0; padding:5px; line-height:1.2; font-size:12px;">

    <h3 style="text-align:center;">කුඩා නගර ජලසම්පාදන</h3>
    <h4 style="text-align:center;">ක්‍රමය</h4>
    <h4 style="text-align:center;">මානව සංවර්ධන සංසදය, පල්ලෙබැද්ද</h4>
    <p style="text-align:center;">0452241148 / 0775881173</p>
    <h4 style="text-align:center;">මාසික බිල්පත</h4>

    <h4 style="text-align:center;">ස්තුතියි !</h4>
    <p style="text-align:center;">© eTechnoLab • 0770 647 647</p>

  </body>
</html>
    `;
}


    },
};
</script>
