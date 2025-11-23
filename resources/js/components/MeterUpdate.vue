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
                    this.printBillTest(html);

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
    <style>
      body {
        width: 300px;
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 10px;
        font-size: 13px;
      }

      .center { text-align: center; }
      .bold { font-weight: bold; }

      .line {
        border-bottom: 1px solid #000;
        margin: 6px 0;
      }

      .double-line {
        border-bottom: 2px solid #000;
        margin: 8px 0;
      }

      .dash {
        border-bottom: 1px dashed #000;
        margin: 8px 0;
      }

      .row {
        display: flex;
        justify-content: space-between;
        margin: 2px 0;
      }

      table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 3px;
      }

      td {
        padding: 3px 0;
      }

      .right { text-align: right; }

      .underline {
        border-bottom: 1px solid #000;
        display: inline-block;
        min-width: 60px;
        padding-bottom: 1px;
      }

      .footer {
        text-align: center;
        margin-top: 15px;
        font-size: 11px;
      }
    </style>
  </head>

  <body>

    <div class="center bold" style="font-size:15px; line-height: 1.3;">
      කුඩා නගර ජලසම්පාදන<br/>
      ක්‍රමය<br/>
      මානව සංවර්ධන සංසදය, පල්ලෙබැද්ද <br/>
    </div>

    <div class="center" style="margin-top:5px;">0452241148 / 0775881173</div>

    <div class="center" style="margin-top:5px;">මාසික බිල්පත</div>

    <div class="line"></div>

    <div class="row">
      <span class="bold">${data.ReadingDate}</span>
    </div>

    <div class="row">
      <span class="bold">${data.InvoiceID}</span>
    </div>

    <div class="dash"></div>

    <div class="row">
      <span>පාරිභෝගික අංකය</span>
      <span class="bold">${data.CustomerCode}</span>
    </div>

    <!-- FIX #1: Add margin bottom -->
    <div class="row" style="margin-bottom:10px;">
      <span>පාරිභෝගික නම</span>
      <span class="bold">${data.CustomerName}</span>
    </div>

    <div class="dash"></div>

    <div class="row bold">
      <span>Avg. Unit</span>
      <span>${data.AvgUnit}.00</span>
    </div>

    <div class="dash"></div>

    <table>
      <tr>
        <td>නව මනු කියවීම</td>
        <td class="right bold">${data.NowReading}</td>
      </tr>

      <tr>
        <td>පෙර මනු කියවීම</td>

        <!-- FIX #2: Underline ONLY the value -->
        <td class="right bold">
          <span class="underline">${data.PreReading}</span>
        </td>
      </tr>

      <tr>
        <td>ජල ඒකක</td>

        <!-- FIX #3: Underline ONLY the value -->
        <td class="right bold">
          <span class="underline">${data.TotalUnits}</span>
        </td>
      </tr>
    </table>

    <br/>

    <table>
      <tr>
        <td>ස්ථාවර ගාස්තුව</td>
        <td class="right bold">${data.FixedAmount}.00</td>
      </tr>

      <tr>
        <td>ඒකක ගාස්තුව</td>
        <td class="right bold">${data.UnitAmount}.00</td>
      </tr>

      <tr>
        <td>වෙනත් - / +</td>

        <!-- FIX #4 underline CrDr -->
        <td class="right bold">
          <span class="underline">${data.CrDR}.00</span>
        </td>
      </tr>

      <tr>
        <td class="bold">බිල්පත් ගාස්තුව</td>

        <!-- FIX #4 underline TotalAmount -->
        <td class="right bold">
          <span class="underline">${data.TotalAmount}.00</span>
        </td>
      </tr>
      </table>

      <br/>

<table>
      <tr>
        <td class="bold">ශේෂය ඉ/ගෙ</td>
        <td class="right bold">${data.LastMonthDue}.00</td>
      </tr>

      <tr>
        <td>ණය වාරික</td>
        <td class="right bold">
        <span class="underline">${data.Installment}.00</span></td>
      </tr>
    </table>



    <div class="row bold" style="font-size:16px; margin-top:5px;">
      <span>ගෙවිය යුතු මුදල</span>

      <!-- FIX #5 double underline -->
      <span class="right">
        <span class="underline" style="border-bottom-width:2px;">${data.TotalDue}.00</span>
      </span>
    </div>

    <br/>

    <div class="double-line"></div>

    <div class="center bold" style="margin-top:8px;">ස්තුතියි !</div>

    <div class="footer">© eTechnoLab • 0770 647 647</div>

  </body>
</html>

    `;
        },
    },
};
</script>
