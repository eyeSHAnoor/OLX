import { useForm } from '@inertiajs/vue3';
import { defineStore } from 'pinia';
import { computed, ref, watch } from 'vue';

export const useBatchStore = defineStore('batches-store', () => {
    const getDefaultCourseForm = (batch = null) => {
        return {
            id: batch?.id ?? '',
            course_id: batch?.course_id ?? '',
            branch_id: batch?.branch_id ?? '',
            delivery_mode: batch?.delivery_mode ?? '',
            reg_opening_date: batch?.reg_opening_date ?? '',
            reg_closing_date: batch?.reg_closing_date ?? '',
            batch_opening_date: batch?.batch_opening_date ?? '',
            batch_closing_date: batch?.batch_closing_date ?? '',
            code: batch?.code ?? '',
            name: batch?.name ?? '',
            intake: batch?.intake ?? '',
            description: batch?.description ?? '',
            medium_of_instruction: batch?.medium_of_instruction ?? '',
            total_seats: batch?.total_seats ?? '',
            is_enforced: batch?.is_enforced ?? false,
            cancelled_at: batch?.cancelled_at ?? '',
        };
    };

    // Initialize with empty defaults
    const form = useForm(getDefaultCourseForm());

    const initializeForm = (course = null) => {
        const newValues = getDefaultCourseForm(course);
        form.defaults(newValues); // update the defaults
        form.reset(); // reset form to new defaults
    };

    const discountTypes = [
        { value: 'standard', label: 'Standard', valid: '', discount_unit: 'percentage', discount_value: 0 },
        { value: 'fp20', label: 'F/P 20% Off', valid: '31-7-2025', discount_unit: 'percentage', discount_value: 20 },
        { value: 'fp10', label: 'F/P 10% Off', valid: '31-7-2025', discount_unit: 'percentage', discount_value: 10 },
    ];

    const selectedDiscountType = computed(() => discountTypes.find((o) => o.value === selectedDiscount.value));

    const selectedNewStudent = ref();
    const selectedPaymentPlan = ref('single');
    const selectedDiscount = ref('standard');
    const installmentCount = ref(0);
    const transformedFees = ref({ headers: [], rows: [] });
    const originalColumnTotals = ref({});
    const feesPlan = ref({});

    // Auto-update totals whenever any values change
    function updateTotals() {
        transformedFees.value.rows.forEach(row => {
            let local = 0;
            let foreign = 0;

            for (const [key, val] of Object.entries(row.values)) {
                const amount = Number(val) || 0;

                if (key.includes('(LKR)')) {
                    local += amount;
                } else if (key.includes('(USD)')) {
                    foreign += amount;
                }
            }

            row.localTotal = local;
            row.foreignTotal = foreign;
        });
    }

    function rebalanceColumn(header, changedIndex) {


        const rows = transformedFees.value.rows;
        const rowCount = rows.length;

        const originalTotal = originalColumnTotals.value?.[header];

        if (originalTotal == null) return;

        const changedValue = Number(rows[changedIndex].values[header]) || 0;

        // If changed value exceeds original total, cap it
        if (changedValue > originalTotal) {

            rows[changedIndex].values[header] = originalTotal;
            for (let i = 0; i < rowCount; i++) {
                if (i !== changedIndex) {
                    rows[i].values[header] = 0;
                }
            }

            updateTotals();
            return;
        }

        const remaining = originalTotal - changedValue;

        // Case 1: editing last row → push rest to first row
        if (changedIndex === rowCount - 1) {

            rows[0].values[header] = remaining;
            for (let i = 1; i < rowCount - 1; i++) {
                if (i !== changedIndex) {
                    rows[i].values[header] = 0;
                }
            }
        } else {

            // Case 2: distribute to remaining rows
            const remainingRows = rowCount - (changedIndex + 1);

            if (remainingRows <= 0) return;

            const base = Math.floor(remaining / remainingRows);
            const remainder = remaining % remainingRows;

            for (let i = changedIndex + 1; i < rowCount; i++) {
                rows[i].values[header] = base + (i - changedIndex <= remainder ? 1 : 0);
            }
        }

        updateTotals();
    }


    // // Optional
    // watch(
    //     () => transformedFees.value.rows.map(r => ({ ...r.values })),
    //     updateTotals,
    //     { deep: true }
    // );


    const installmentsPlan = computed(() => {
        return transformedFees.value?.rows?.flatMap((row) => {
            return Object.entries(row.values).map(([key, amount]) => {
                const [label, currencyPart] = key.split(' (');
                const currency = currencyPart?.replace(')', '');
                const type = currency === 'USD' ? 'foreign' : 'local';

                return {
                    installment: row.installment,
                    label: label.trim(),
                    amount: Number(amount),
                    type,
                    currency,
                    due_date: row.dueDate, // optional if you want to save it
                };
            });
        }) ?? [];
    });

    watch(
        () => [selectedPaymentPlan.value, selectedNewStudent.value],
        () => {
            installmentCount.value = selectedPaymentPlan.value === 'single' ? 1 : 0;
        },
    );




    return {
        form,
        initializeForm,

        selectedNewStudent,
        selectedPaymentPlan,

        discountTypes,
        selectedDiscount,
        selectedDiscountType,
        installmentCount,

        transformedFees,
        updateTotals,
        originalColumnTotals,
        rebalanceColumn,

        installmentsPlan,
        feesPlan,
    };
});
