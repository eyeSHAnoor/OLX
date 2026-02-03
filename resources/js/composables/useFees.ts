import { addMonths, format, parseISO } from 'date-fns';

type DiscountData = {
    discount_value?: number | string;
    discount_unit?: 'percentage' | 'amount';
    id: number | null;
    course_id: number | null;
    label: string | null;
    start_date: string | null;
    end_date: string | null;
    is_rms_enabled: boolean | null;
    status: string | null;
};

type CourseInstallment = {
    installment_no: number;
    label: string;
    amount: number;
    currency: 'LKR' | 'USD' | 'EUR';
    type: 'local' | 'foreign';
    taxes?: number;
    due_date?: string;
    installment_allowed?: boolean;
};

type InstallmentRow = {
    installment: number;
    installmentLabel: string;
    dueDate?: string;
    values: Record<string, { amount: number; installmentAllowed: boolean }>;
};

export function calculateDiscount(amount: number, discountData?: DiscountData | null): number {
    if (!discountData || !discountData.discount_value) return 0;

    const value = parseFloat(discountData.discount_value as string);

    if (discountData.discount_unit === 'percentage') {
        return Math.round((amount * value) / 100);
    } else {
        return value || 0;
    }
}

export function totalInstallmentNos(installments: CourseInstallment[]) {
    return [...new Set(installments.map((item) => item.installment_no))];
}

export function transformFeesForInstallments(
    installments: CourseInstallment[],
    startDueDates?: string,
    selectedDiscount?: DiscountData | null,
): {
    headers: string[];
    rows: InstallmentRow[];
    originalColumnTotals: Record<string, number>;
} {
    if (!installments.length) {
        return { headers: [], rows: [], originalColumnTotals: {} };
    }

    const totalInstallmentsList = totalInstallmentNos(installments);

    const labelCurrencyKeys = Array.from(new Set(installments.map((i) => `${i.label} (${i.currency})`)));

    const headers = ['Installment', ...labelCurrencyKeys, 'Due Date'];

    const grouped = installments.reduce<Record<number, CourseInstallment[]>>((acc, curr) => {
        acc[curr.installment_no] = acc[curr.installment_no] || [];
        acc[curr.installment_no].push(curr);
        return acc;
    }, {});

    const rows: InstallmentRow[] = [];

    Object.keys(grouped).forEach((key, index) => {
        const installmentNo = Number(key);
        const group = grouped[installmentNo];

        let dueDate = group[0]?.due_date || '';
        if (startDueDates) {
            const start = parseISO(startDueDates);
            const nextDate = addMonths(start, index);
            dueDate = format(nextDate, 'yyyy-MM-dd');
        }

        const row: InstallmentRow = {
            installment: installmentNo,
            installmentLabel: totalInstallmentsList?.length == 1 ? 'Full Payment' : `Installment ${installmentNo}`,
            dueDate,
            values: {},
        };

        group.forEach((installment) => {
            const columnKey = `${installment.label} (${installment.currency})`;
            let amount = installment.amount;
            let discountedAmount = amount;

            // Apply discount only to Course Fee Only
            if (
                installment.label?.toLowerCase().includes('course fee') &&
                (installment.currency === 'LKR' || installment.label?.toLowerCase() === 'local')
            ) {
                if (selectedDiscount?.discount_unit === 'percentage') {
                    const discountAmount = calculateDiscount(amount, selectedDiscount);
                    amount = Math.max(0, amount - discountAmount);
                } else if (selectedDiscount?.discount_unit === 'amount') {
                    const discountAmount = selectedDiscount?.discount_value / totalInstallmentsList?.length;
                    amount = Math.max(0, amount - discountAmount);
                    // console.log( discountAmount, selectedDiscount?.discount_value, totalInstallmentNos?.length,);
                }
            }

            row.values[columnKey] = {
                amount,
                installmentAllowed: installment.installment_allowed ?? true,
                // status: installment.status,
            };
        });

        rows.push(row);
    });

    const originalColumnTotals: Record<string, number> = {};

    labelCurrencyKeys.forEach((key) => {
        originalColumnTotals[key] = installments.filter((i) => `${i.label} (${i.currency})` === key).reduce((sum, i) => sum + i.amount, 0);
    });

    return {
        headers,
        rows,
        originalColumnTotals,
    };
}

// const totalLocalAmount = computed(() => localFees.value?.reduce((sum, item) => sum + item.amount, 0) || 0);
// const totalForeignAmount = computed(() => foreignFees.value?.reduce((sum, item) => sum + item.amount, 0) || 0);

export function getTotalForColumn(transformedInstallments: ReturnType<typeof transformFeesForInstallments>, label: string): number {
    return transformedInstallments.rows.reduce((sum, row) => {
        const cell = row.values[label];
        return sum + (cell?.amount ?? 0);
    }, 0);
}

// const getGrandTotal = () => {
//     const localTotal = localFees.value?.reduce((sum, fee) => sum + fee.amount, 0) || 0;
//     const foreignTotal = foreignFees.value?.reduce((sum, fee) => sum + fee.amount, 0) || 0;
//     return { localTotal, foreignTotal, combinedTotal: localTotal + foreignTotal };
// };
//
// const getNetGrandTotal = () => {
//     const originalLocalTotal = localFees.value?.reduce((sum, fee) => sum + fee.amount, 0) || 0;
//     const originalForeignTotal = foreignFees.value?.reduce((sum, fee) => sum + fee.amount, 0) || 0;
//
//     const localCourseFee = localFees.value?.find((fee) => fee.label === 'Course Fee')?.amount || 0;
//     const localCourseDiscount = selectedDiscount.value ? calculateDiscount(localCourseFee, selectedDiscount.value) : 0;
//
//     return {
//         localNetTotal: originalLocalTotal - localCourseDiscount,
//         foreignNetTotal: originalForeignTotal,
//         combinedNetTotal: originalLocalTotal - localCourseDiscount + originalForeignTotal,
//     };
// };

