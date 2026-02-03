import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import _ from 'lodash';

export default function useSearchFilter(searchUrl, additionalFilters = {}) {

  const processing = ref(false);
  const queryFilterParams = ref('');
  const uri = location.search.substring(1);
  const queryParams = new URLSearchParams(uri);
  queryFilterParams.value = queryParams.get('filter[global]');

  const form = ref({
    filter: { global: queryFilterParams.value ?? '' },
    ...additionalFilters,
  });

  const isFiltered = computed(() => {
    // Extract the filter object and other properties (excluding page/perPage)
    const { page, perPage, ...filtersToCheck } = form.value;

    // Check if any filter has a value
    return Object.entries(filtersToCheck).some(([key, value]) => {
      if (value === null || value === undefined) return false;

      // Check for non-empty strings
      if (typeof value === 'string') return value.trim() !== '';

      // Check for non-empty objects (like `filter: { global: ... }`)
      if (typeof value === 'object' && !Array.isArray(value)) {
        return Object.values(value).some(innerValue => {
          if (innerValue === null || innerValue === undefined) return false;
          if (typeof innerValue === 'string') return innerValue.trim() !== '';
          return true; // Other non-empty values (numbers, booleans, etc.)
        });
      }

      // Check for non-empty arrays
      if (Array.isArray(value)) return value.length > 0;

      // Other truthy values (numbers, booleans, etc.)
      return true;
    });
  });

  watch(
    () => form.value,
    () => {
      processing.value = true;

      if (typeof window.LIT !== 'undefined') {
        clearTimeout(window.LIT);
      }

      window.LIT = setTimeout(() => {
        router.get(searchUrl, _.pickBy(form.value), {
          preserveState: true,
          preserveScroll: true,
          replace: true,
          onFinish: (() => processing.value = false),
        });
      }, 700);
    },
    {
      deep: true,
      // immediate: true
    }
  );

  const onSort = (col) => {
    // console.log(col);     // return; 

    if (!col?.sortable) return;

    form.value.sort = form.value.sort !== col.key ? col.key : `-${col.key}`;
  };

  const reset = () => {
    // form.value = _.mapValues(form.value, () => null);
    // form.value.filter = { global: '' };
    form.value = {
      filter: { global: null },
      ...additionalFilters,
    }
  };



  const dateRange = ref('');

  watch(
    () => dateRange.value,
    (newValue, oldValue) => {
      if (dateRange.value && dateRange.value.length) {
        form.value.start_date = dateRange.value[0] ?? null;
        form.value.end_date = dateRange.value[1] ?? null;
      } else {
        form.value.start_date = form.value.end_date = null;
      }
    }
  );


  return {
    form,
    queryParams,
    reset,
    onSort,
    processing,
    dateRange,
    isFiltered,
  };
}
