<template>
    <AppLayout :title="isEdit ? 'Edit City' : 'Create City'">
        <AppContainer>
            <!-- Page Header -->
            <div class="my-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">
                            {{ isEdit ? `Edit: ${form.name}` : 'Create New City' }}
                        </h1>
                        <p class="text-muted-foreground mt-2">
                            {{ isEdit ? 'Update city details and regions' : 'Fill in the details to create a new city'
                            }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <AppButton label="Cancel" variant="outline" @click="cancel" :disabled="form.processing" />
                        <AppButton :label="isEdit ? 'Update City' : 'Create City'" icon="lucide:check"
                            :processing="form.processing" @click="submit"
                            class="bg-brand-orange hover:bg-brand-orange/80" />
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Form -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Info Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                            <CardDescription>General details about the city</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <ValidationErrors :errors="form.errors" />

                            <TextInput label="City Name *" v-model="form.name" :error="form.errors.name"
                                placeholder="Enter city name" required />

                            <TextInput label="Country *" v-model="form.country" :error="form.errors.country"
                                placeholder="Enter country name" required />

                            <div class="grid grid-cols-2 gap-4">
                                <TextInput label="Latitude" v-model="form.lat" :error="form.errors.lat"
                                    placeholder="e.g., 40.7128" type="number" step="any" />
                                <TextInput label="Longitude" v-model="form.lng" :error="form.errors.lng"
                                    placeholder="e.g., -74.0060" type="number" step="any" />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Regions Card -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <div>
                                    <CardTitle>City Regions</CardTitle>
                                    <CardDescription>Add districts, boroughs, or neighborhoods within this city
                                    </CardDescription>
                                </div>
                                <AppButton variant="outline" size="sm" @click="addRegion">
                                    <Plus class="size-4 mr-2" />
                                    Add Region
                                </AppButton>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div v-if="form.regions.length === 0"
                                class="text-center py-8 border-2 border-dashed rounded-md">
                                <Icon icon="lucide:map-pin" class="size-8 text-muted-foreground mx-auto mb-2" />
                                <p class="text-sm text-muted-foreground">No regions added yet</p>
                                <p class="text-xs text-muted-foreground mt-1">
                                    Click "Add Region" to add districts, boroughs, or neighborhoods
                                </p>
                            </div>

                            <div v-else class="space-y-3">
                                <div v-for="(region, index) in form.regions" :key="index"
                                    class="flex items-center gap-3 p-3 border rounded-lg hover:bg-muted/50">
                                    <div class="flex-1">
                                        <TextInput v-model="region.name"
                                            placeholder="Region name (e.g., Downtown, Northside)" size="sm"
                                            :error="form.errors[`regions.${index}.name`]" />
                                    </div>
                                    <button type="button" @click="removeRegion(index)"
                                        class="text-red-500 hover:text-red-700 p-1" title="Remove region">
                                        <Icon icon="lucide:trash-2" class="size-4" />
                                    </button>
                                </div>
                            </div>

                            <!-- Tips for regions -->
                            <div v-if="form.regions.length > 0" class="mt-4 p-3 bg-blue-50 rounded-md">
                                <div class="flex items-start gap-2">
                                    <Icon icon="lucide:info" class="size-4 text-blue-500 mt-0.5" />
                                    <div class="text-xs text-blue-700">
                                        <p class="font-medium mb-1">Tips for regions:</p>
                                        <ul class="list-disc list-inside space-y-0.5">
                                            <li>Add common districts or neighborhoods (e.g., Manhattan, Brooklyn)</li>
                                            <li>Include postal code areas if useful for filtering</li>
                                            <li>Regions help users find products or services in specific areas</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Actions Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex flex-col gap-2">
                                <AppButton :label="isEdit ? 'Update City' : 'Create City'" icon="lucide:check"
                                    :processing="form.processing" @click="submit"
                                    class="bg-brand-orange hover:bg-brand-orange/80 w-full justify-center" />

                                <AppButton label="Cancel" variant="outline" class="w-full justify-center"
                                    @click="cancel" :disabled="form.processing" />

                                <AppButton v-if="isEdit" label="Delete City" variant="danger" icon="lucide:trash-2"
                                    class="w-full justify-center" @click="destroy" :disabled="form.processing" />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Preview Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Preview</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div class="bg-muted rounded-lg p-4 text-center">
                                    <Icon icon="lucide:building-2" class="size-12 text-muted-foreground mx-auto mb-2" />
                                    <h3 class="font-semibold text-lg">{{ form.name || 'City Name' }}</h3>
                                    <div class="text-sm text-muted-foreground">{{ form.country || 'Country' }}</div>
                                    <div v-if="form.lat && form.lng" class="text-xs text-muted-foreground mt-1">
                                        {{ form.lat }}, {{ form.lng }}
                                    </div>
                                </div>

                                <div v-if="form.regions.length > 0" class="pt-2">
                                    <div class="text-xs font-medium text-muted-foreground mb-2">Regions:</div>
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="(region, idx) in form.regions.slice(0, 5)" :key="idx"
                                            class="px-2 py-1 bg-primary/5 text-primary rounded-md text-xs">
                                            {{ region.name }}
                                        </span>
                                        <span v-if="form.regions.length > 5" class="text-xs text-muted-foreground">
                                            +{{ form.regions.length - 5 }} more
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Tips Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-sm">Tips for cities</CardTitle>
                        </CardHeader>
                        <CardContent class="text-sm space-y-2 text-muted-foreground">
                            <div class="flex items-start gap-2">
                                <Icon icon="lucide:check-circle" class="size-4 text-green-500 mt-0.5" />
                                <span>Use official city names (e.g., "New York" not "NYC")</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <Icon icon="lucide:check-circle" class="size-4 text-green-500 mt-0.5" />
                                <span>Add country to avoid ambiguity (e.g., "Paris, France")</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <Icon icon="lucide:check-circle" class="size-4 text-green-500 mt-0.5" />
                                <span>Latitude/Longitude help with map integrations</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <Icon icon="lucide:check-circle" class="size-4 text-green-500 mt-0.5" />
                                <span>Regions help users narrow down to specific areas</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppContainer>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Card from '@/components/ui/card/Card.vue'
import CardHeader from '@/components/ui/card/CardHeader.vue'
import CardTitle from '@/components/ui/card/CardTitle.vue'
import CardDescription from '@/components/ui/card/CardDescription.vue'
import CardContent from '@/components/ui/card/CardContent.vue'
import TextInput from '@/components/TextInput.vue'
import ValidationErrors from '@/components/ValidationErrors.vue'
import AppButton from '@/components/AppButton.vue'
import { Icon } from '@iconify/vue'
import { useAlertDialog } from '@/composables/useAlertDialog'
import { useBreadcrumb } from '@/composables/useBreadcrumb'
import { Plus } from 'lucide-vue-next'

const props = defineProps<{
    city?: App.Data.CityData & {
        regions?: Array<{ id: number; name: string }>
    }
}>()

const isEdit = computed(() => !!props.city)

// Form setup
const getDefaultForm = () => ({
    name: props.city?.name ?? '',
    country: props.city?.country ?? '',
    lat: props.city?.lat ?? '',
    lng: props.city?.lng ?? '',
    regions: props.city?.regions?.map(region => ({
        id: region.id,
        name: region.name
    })) ?? [] as Array<{ id?: number; name: string }>
})

const form = useForm(getDefaultForm())

// Region methods
const addRegion = () => {
    form.regions.push({ name: '' })
}

const removeRegion = (index: number) => {
    form.regions.splice(index, 1)
}

// Submit form
const submit = () => {
    const formData = new FormData()

    // Basic fields
    formData.append('name', form.name)
    formData.append('country', form.country)
    if (form.lat !== undefined && form.lat !== '') formData.append('lat', String(form.lat))
    if (form.lng !== undefined && form.lng !== '') formData.append('lng', String(form.lng))

    // Regions
    form.regions.forEach((region, index) => {
        if (region.id) {
            formData.append(`regions[${index}][id]`, String(region.id))
        }
        if (region.name.trim()) {
            formData.append(`regions[${index}][name]`, region.name)
        }
    })

    if (isEdit.value && props.city?.id) {
        formData.append('_method', 'PUT')
        router.post(route('cities.update', props.city.id), formData, {
            onSuccess: () => {
                router.visit(route('cities.index'))
            },
            onError: (errors) => {
                form.setError(errors)
            }
        })
    } else {
        router.post(route('cities.store'), formData, {
            onSuccess: () => {
                router.visit(route('cities.index'))
            },
            onError: (errors) => {
                form.setError(errors)
            }
        })
    }
}

const cancel = () => {
    router.visit(route('cities.index'))
}

const alert = useAlertDialog()
const destroy = async () => {
    if (!props.city?.id) return

    const confirmed = await alert.show({
        title: 'Delete City',
        description: `Are you sure you want to delete "${props.city.name}"? This will also delete all associated regions.`,
        confirmText: 'Yes, Delete',
        cancelText: 'Cancel',
    })

    if (confirmed) {
        router.delete(route('cities.destroy', props.city.id), {
            onSuccess: () => {
                router.visit(route('cities.index'))
            }
        })
    }
}

// Breadcrumb
const { set, resetList } = useBreadcrumb()
onMounted(() => {
    resetList()
    set([
        { label: 'Dashboard', href: '/dashboard' },
        { label: 'Cities', href: route('cities.index') },
        { label: isEdit.value ? 'Edit City' : 'Create City', href: '#' }
    ])
})
</script>