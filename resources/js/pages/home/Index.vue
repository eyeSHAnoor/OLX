<template>
  <OlxLayout>
    <!-- SEO & Favicon -->

    <Head>
      <title>Amo mercatus - Buy & Sell New & Used Items</title>
      <meta
        name="description"
        content="Find great deals on new and used items in your city. Post free ads, buy and sell electronics, cars, furniture, and more."
      />
      <meta
        name="keywords"
        content="classifieds, buy sell, marketplace, used items, free ads"
      />
      <meta property="og:title" content="Marketplace - Best Local Deals" />
      <meta
        property="og:description"
        content="Find great deals on new and used items in your city."
      />
      <meta property="og:type" content="website" />
      <link rel="icon" type="image/x-icon" href="/favicon.ico" />
      <link rel="shortcut icon" href="/favicon.ico" />
    </Head>

    <TopCategoriesBar />

    <!-- HERO BANNER -->
    <section
      v-if="homepageBanners.length"
      class="relative bg-gray-100 h-[180px] md:h-[400px] lg:h-[500px] overflow-hidden"
    >
      <div
        v-for="(banner, index) in homepageBanners"
        :key="banner.id"
        class="absolute inset-0 transition-opacity duration-700"
        :class="{
          'opacity-100 z-10': currentSlide === index,
          'opacity-0': currentSlide !== index,
        }"
      >
        <a
          :href="banner.link || '#'"
          :target="banner.link ? '_blank' : '_self'"
          class="block w-full h-full"
        >
          <img
            :src="banner.image_url"
            :alt="banner.title"
            class="w-full h-full object-contain"
            loading="lazy"
          />
        </a>
      </div>

      <button
        v-if="homepageBanners.length > 1"
        @click="prevSlide"
        class="absolute left-4 top-1/2 -translate-y-1/2 z-20 bg-white/90 rounded-full p-3"
      >
        <Icon icon="mdi:chevron-left" class="text-2xl" />
      </button>
      <button
        v-if="homepageBanners.length > 1"
        @click="nextSlide"
        class="absolute right-4 top-1/2 -translate-y-1/2 z-20 bg-white/90 rounded-full p-3"
      >
        <Icon icon="mdi:chevron-right" class="text-2xl" />
      </button>

      <div
        v-if="homepageBanners.length > 1"
        class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex gap-2"
      >
        <button
          v-for="(_, index) in homepageBanners"
          :key="index"
          @click="currentSlide = index"
          class="h-2 rounded-full transition-all"
          :class="currentSlide === index ? 'w-8 bg-brand-teal' : 'w-2 bg-white/70'"
        ></button>
      </div>
    </section>

    <!-- MAIN CONTENT -->
    <template v-if="!isSearching">
      <!-- BROWSE CATEGORIES SECTION -->
      <section class="py-8 bg-gray-50">
        <div class="max-w-full md:max-w-8/10 mx-auto px-4 md:px-3">
          <h2 class="text-lg md:text-xl font-semibold mb-6 text-center">
            Browse Categories
          </h2>

          <!-- Desktop grid (unchanged) -->
          <div class="grid grid-cols-4 md:grid-cols-7 gap-4">
            <div
              v-for="category in displayCategories"
              :key="category.id"
              v-memo="[category.id, category.files?.[0]?.file_url]"
              @click="navigateToCategory(category)"
              class="flex flex-col items-center cursor-pointer group"
            >
              <div
                class="w-full aspect-square rounded-xl overflow-hidden bg-white shadow-sm group-hover:shadow-md transition"
              >
                <img
                  v-if="category.files?.length"
                  :src="category.files[0].file_url"
                  class="w-full h-full object-cover"
                  :alt="category.name"
                  loading="lazy"
                />
                <div
                  v-else
                  class="w-full h-full flex items-center justify-center text-gray-400"
                >
                  <Icon icon="mdi:image-off" class="text-3xl" />
                </div>
              </div>
              <span
                class="mt-2 text-xs md:text-sm font-medium text-center line-clamp-2 text-gray-800"
              >
                {{ category.name }}
              </span>
            </div>
          </div>

          <!-- MOBILE / TABLET: carousel -->
          <!-- <div class="md:hidden">
            <div class="relative">
              <div
                ref="carouselContainer"
                class="overflow-x-auto snap-x snap-mandatory scrollbar-none"
                @scroll="throttledCarouselScroll"
              >
                <div
                  class="grid grid-rows-2 grid-flow-col gap-1 w-max"
                  style="grid-auto-columns: 80px"
                >
                  <div
                    v-for="category in displayCategories"
                    :key="category.id"
                    v-memo="[category.id, category.files?.[0]?.file_url]"
                    @click="handleCategoryClick(category)"
                    class="snap-start cursor-pointer group transition-transform hover:scale-105"
                  >
                    <div class="flex flex-col items-center">
                      <div
                        class="w-full aspect-square rounded-xl overflow-hidden bg-white shadow-sm group-hover:shadow-md transition"
                      >
                        <img
                          v-if="category.files?.length"
                          :src="category.files[0].file_url"
                          class="w-full h-full object-cover"
                          :alt="category.name"
                          loading="lazy"
                        />
                        <div
                          v-else
                          class="w-full h-full flex items-center justify-center text-gray-400"
                        >
                          <Icon icon="mdi:image-off" class="text-xl" />
                        </div>
                      </div>
                      <span
                        class="mt-1 text-[10px] font-medium text-center line-clamp-2 text-gray-800"
                      >
                        {{ category.name }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div> -->
        </div>
      </section>

      <!-- RECENTLY VIEWED -->
      <section v-if="recentAds.length" class="py-8 bg-white border-b border-gray-100">
        <div class="max-w-full md:max-w-8/10 mx-auto px-4 md:px-3">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold flex items-center gap-2">
              <Icon icon="mdi:history" class="text-2xl text-yellow-500" />
              Recently Viewed
            </h2>
          </div>
          <div class="relative">
            <button
              v-if="showPrevButton"
              @click="scrollPrev"
              class="hidden md:flex absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-white/80 rounded-full p-2 shadow-md hover:bg-white focus:outline-none"
              :class="{ hidden: !canScrollLeftRecent }"
            >
              <Icon icon="mdi:chevron-left" class="w-5 h-5" />
            </button>
            <button
              v-if="showNextButton"
              @click="scrollNext"
              class="hidden md:flex absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-white/80 rounded-full p-2 shadow-md hover:bg-white focus:outline-none"
              :class="{ hidden: !canScrollRightRecent }"
            >
              <Icon icon="mdi:chevron-right" class="w-5 h-5" />
            </button>
            <div
              ref="recentScrollContainer"
              class="overflow-x-auto overflow-y-hidden pb-4 -mx-4 px-4 scrollbar-hide"
              style="
                scroll-behavior: smooth;
                scrollbar-width: none;
                -ms-overflow-style: none;
              "
              @scroll="throttledRecentScroll"
            >
              <div class="flex flex-nowrap gap-4">
                <div
                  v-for="ad in recentAds"
                  :key="ad.id"
                  class="flex-shrink-0 w-[260px] sm:w-[calc(50%-0.5rem)] md:w-[calc(33.333%-0.666rem)] lg:w-[calc(25%-0.75rem)]"
                >
                  <AdCard :ad="ad" :size="'normal'" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- PROMOTIONAL BANNERS -->
      <section v-if="promotionalBanners.length" class="py-5">
        <div class="max-w-7xl mx-auto px-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <a
              v-for="banner in promotionalBanners"
              :key="banner.id"
              :href="banner.link || '#'"
              class="block overflow-hidden rounded-xl h-48 md:h-56"
            >
              <img
                :src="banner.image_url"
                :alt="banner.title"
                class="w-full h-full object-contain"
                loading="lazy"
              />
            </a>
          </div>
        </div>
      </section>

      <!-- CATEGORY ADS (heavy component – keep an eye on this) -->
      <section class="max-w-full md:max-w-8/10 mx-auto px-4 md:px-3 space-y-12 pb-20">
        <CategoryAds v-for="cat in topCategories" :key="cat.id" :category="cat" />
      </section>

      <!-- BOTTOM BANNER -->
      <section v-if="bottomBanner" class="relative h-64 md:h-80 overflow-hidden">
        <a :href="bottomBanner.link || '#'" class="block w-full h-full">
          <img
            :src="bottomBanner.image_url"
            :alt="bottomBanner.title"
            class="w-full h-full object-cover"
            loading="lazy"
          />
        </a>
      </section>
    </template>

    <template v-else>
      <div class="max-w-7xl mx-auto px-4 py-4">
        <button @click="goBack" class="flex items-center px-5 py-3 text-gray-700">
          <Icon icon="mdi:arrow-left" class="text-4xl mr-2" />
          Back to Home
        </button>
      </div>
    </template>

    <!-- MOBILE SUB-CATEGORY SIDEBAR (stable, no animation) -->
    <Teleport to="body">
      <div
        v-if="selectedParentCategory"
        class="fixed inset-0 z-[100] flex"
        @click.self="closeSidebar"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50"></div>

        <!-- Sidebar panel -->
        <div class="relative w-4/5 max-w-xs bg-white h-full shadow-xl overflow-y-auto">
          <!-- Header -->
          <div class="flex items-center justify-between p-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800 truncate">
              {{ selectedParentCategory.name }}
            </h3>
            <button @click="closeSidebar" class="p-1 hover:bg-gray-100 rounded-full">
              <Icon icon="mdi:close" class="text-2xl text-gray-600" />
            </button>
          </div>

          <!-- Subcategory list -->
          <div class="p-2">
            <button
              v-for="child in selectedParentCategory.children"
              :key="child.id"
              @click="navigateToCategory(child)"
              class="w-full text-left px-4 py-3 rounded-lg hover:bg-gray-100 transition flex items-center gap-3"
            >
              <Icon icon="mdi:chevron-right" class="text-gray-400" />
              <span class="text-sm font-medium">{{ child.name }}</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </OlxLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { Head, usePage, router } from "@inertiajs/vue3";
import OlxLayout from "@/layouts/OlxLayout.vue";
import CategoryAds from "@/components/CategoryAds.vue";
import AdCard from "@/components/AdCard.vue";
import { Icon } from "@iconify/vue";
import { useForceTheme } from "@/composables/useForceTheme";

// --- Page data ---
const page = usePage();
const categories = computed(() => page.props.categories || []);
const banners = computed(() => page.props.banners || []);
const isSearching = computed(() => page.props.isSearching || false);
const recentAds = computed(() => page.props.recentAds || []);

console.log(page.props);

const displayCategories = computed(() => categories.value.slice(0, 20));

// --- Mobile sidebar (stable) ---
const selectedParentCategory = ref<any>(null);

function openSidebar(category: any) {
  // Only allow on screens smaller than 768px and when there are subcategories
  if (window.innerWidth < 768 && category.children?.length) {
    selectedParentCategory.value = category;
  }
}

function closeSidebar() {
  selectedParentCategory.value = null;
}

function selectSubCategory(child: any) {
  closeSidebar();
  router.get(route("category.show", { slug: child.slug }));
}

// Auto close sidebar if window is resized to desktop size
function onWindowResize() {
  if (window.innerWidth >= 768 && selectedParentCategory.value) {
    closeSidebar();
  }
}

// Click handler for mobile categories
function handleCategoryClick(category: any) {
  if (window.innerWidth < 768 && category.children?.length) {
    openSidebar(category);
  } else {
    navigateToCategory(category);
  }
}

// --- Hero carousel (unchanged) ---
const currentSlide = ref(0);
let interval: ReturnType<typeof setInterval> | null = null;

const homepageBanners = computed(() =>
  banners.value.filter((b) => b.position === "homepage")
);
const promotionalBanners = computed(() =>
  banners.value.filter((b) => ["category", "sidebar"].includes(b.position)).slice(0, 2)
);
const bottomBanner = computed(() => banners.value.find((b) => b.position === "floating"));

const nextSlide = () => {
  if (homepageBanners.value.length) {
    currentSlide.value = (currentSlide.value + 1) % homepageBanners.value.length;
  }
};

const prevSlide = () => {
  if (homepageBanners.value.length) {
    currentSlide.value =
      (currentSlide.value - 1 + homepageBanners.value.length) %
      homepageBanners.value.length;
  }
};

const startAutoPlay = () => {
  if (homepageBanners.value.length > 1 && !interval) {
    interval = setInterval(nextSlide, 5000);
  }
};

const stopAutoPlay = () => {
  if (interval) {
    clearInterval(interval);
    interval = null;
  }
};

const handleVisibilityChange = () => {
  if (document.hidden) {
    stopAutoPlay();
  } else {
    startAutoPlay();
  }
};

// --- General methods ---
const topCategories = computed(() =>
  categories.value.filter((c) => !c.parent_id).slice(0, 7)
);

const navigateToCategory = (category: any) => {
  console.log("Navigating to category:", category);
  if (category.slug) {
    router.get(route("category.show", { slug: category.slug }));
  }
};

const goBack = () => {
  router.get(route("home"));
};

// --- Categories carousel logic (unchanged) ---
const carouselContainer = ref<HTMLElement | null>(null);
const canScrollLeftCarousel = ref(false);
const canScrollRightCarousel = ref(false);

const itemsPerPage = computed(() => {
  const el = carouselContainer.value;
  if (!el) return 8;
  const cardWidth = 80 + 4;
  return Math.floor(el.clientWidth / cardWidth) * 2;
});
const totalPages = computed(() =>
  Math.ceil(categories.value.length / itemsPerPage.value)
);

const currentPage = computed(() => {
  const el = carouselContainer.value;
  if (!el || totalPages.value === 0) return 0;
  const scrollPos = el.scrollLeft;
  const pageWidth = el.clientWidth;
  let page = Math.round(scrollPos / pageWidth);
  page = Math.min(page, totalPages.value - 1);
  return Math.max(0, page);
});

let carouselScrollRAF: number | null = null;
const updateCarouselScroll = () => {
  const el = carouselContainer.value;
  if (!el) return;
  const scrollLeft = el.scrollLeft;
  const maxScroll = el.scrollWidth - el.clientWidth;
  canScrollLeftCarousel.value = scrollLeft > 10;
  canScrollRightCarousel.value = maxScroll - scrollLeft > 10;
};

const throttledCarouselScroll = () => {
  if (carouselScrollRAF) return;
  carouselScrollRAF = requestAnimationFrame(() => {
    updateCarouselScroll();
    carouselScrollRAF = null;
  });
};

const scrollToPage = (pageIndex: number) => {
  const el = carouselContainer.value;
  if (el) {
    el.scrollTo({ left: pageIndex * el.clientWidth, behavior: "smooth" });
  }
};

watch(
  () => categories.value.length,
  () => {
    setTimeout(() => {
      if (carouselContainer.value) {
        carouselContainer.value.scrollLeft = 0;
        updateCarouselScroll();
      }
    }, 100);
  }
);

// --- Recently viewed carousel logic (unchanged) ---
const recentScrollContainer = ref<HTMLElement | null>(null);
const canScrollLeftRecent = ref(false);
const canScrollRightRecent = ref(false);

const updateRecentScrollButtons = () => {
  const el = recentScrollContainer.value;
  if (!el) return;
  const { scrollLeft, scrollWidth, clientWidth } = el;
  canScrollLeftRecent.value = scrollLeft > 20;
  canScrollRightRecent.value = scrollLeft + clientWidth < scrollWidth - 20;
};

let recentScrollRAF: number | null = null;
const throttledRecentScroll = () => {
  if (recentScrollRAF) return;
  recentScrollRAF = requestAnimationFrame(() => {
    updateRecentScrollButtons();
    recentScrollRAF = null;
  });
};

const scrollPrev = () => {
  recentScrollContainer.value?.scrollBy({ left: -300, behavior: "smooth" });
};

const scrollNext = () => {
  recentScrollContainer.value?.scrollBy({ left: 300, behavior: "smooth" });
};

const showPrevButton = true;
const showNextButton = true;

// --- Keyboard handler for sidebar ---
function handleEscape(e: KeyboardEvent) {
  if (e.key === "Escape" && selectedParentCategory.value) {
    closeSidebar();
  }
}

// --- Lifecycle ---
onMounted(() => {
  useForceTheme("light");

  startAutoPlay();
  document.addEventListener("visibilitychange", handleVisibilityChange);

  const heroCarousel = document.querySelector(".relative.bg-gray-100");
  heroCarousel?.addEventListener("mouseenter", stopAutoPlay);
  heroCarousel?.addEventListener("mouseleave", startAutoPlay);

  carouselContainer.value?.addEventListener("scroll", throttledCarouselScroll, {
    passive: true,
  });
  updateCarouselScroll();

  recentScrollContainer.value?.addEventListener("scroll", throttledRecentScroll, {
    passive: true,
  });
  window.addEventListener("resize", throttledRecentScroll, { passive: true });
  updateRecentScrollButtons();

  // Sidebar resize listener
  window.addEventListener("resize", onWindowResize);
  document.addEventListener("keydown", handleEscape);
});

onUnmounted(() => {
  stopAutoPlay();
  document.removeEventListener("visibilitychange", handleVisibilityChange);

  const heroCarousel = document.querySelector(".relative.bg-gray-100");
  heroCarousel?.removeEventListener("mouseenter", stopAutoPlay);
  heroCarousel?.removeEventListener("mouseleave", startAutoPlay);

  carouselContainer.value?.removeEventListener("scroll", throttledCarouselScroll);
  recentScrollContainer.value?.removeEventListener("scroll", throttledRecentScroll);
  window.removeEventListener("resize", throttledRecentScroll);

  window.removeEventListener("resize", onWindowResize);
  document.removeEventListener("keydown", handleEscape);

  if (carouselScrollRAF) cancelAnimationFrame(carouselScrollRAF);
  if (recentScrollRAF) cancelAnimationFrame(recentScrollRAF);
});
</script>
