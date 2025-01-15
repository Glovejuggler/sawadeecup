<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import colors from 'tailwindcss/colors';

const tc = Object.keys(colors)
    .filter(key => typeof colors[key] === 'object')

const props = defineProps({
    item: Object,
    categories: Object,
    suggestions: Object,
})

const form = useForm({
    category_id: props.item.category_id,
    image: '',
    name: props.item.name,
    price: props.item.price,
    color: props.item.color,
    breakdown: props.item.costing
})

const costValue = computed(() => {
    return form.breakdown.reduce((acc, item) => acc + Number(item.cost), 0)
})
const profit = computed(() => (Number(form.price) - costValue.value).toFixed(2))

const selectedColor = ref(form.color)
const newImage = ref(null)
const imgTmp = ref(null)
const showImage = () => {
    if (form.image) {
        let reader = new FileReader();
        reader.readAsDataURL(form.image)

        reader.onload = (e) => {
            imgTmp.value = e.target.result
        }
    }
}

const addBreakdown = () => {
    form.breakdown.push({
        name: '',
        cost: '',
    })
}

const removeBreakdown = (index) => {
    form.breakdown.splice(index, 1)
}

const showDeleteConfirmationModal = ref(false)
</script>

<template>

    <Head>
        <title>
            {{ item.name }}
        </title>
    </Head>

    <div class="max-w-screen-lg mx-auto mt-8">
        <p class="dark:text-white mb-8">{{ item.name }}</p>
        <div class="lg:grid grid-cols-3 gap-4">
            <div>
                <div @click="newImage.click()"
                    class="h-72 w-72 rounded-lg overflow-hidden bg-white dark:bg-zinc-700 flex justify-center items-center relative group">
                    <div v-if="item.pic"
                        class="absolute inset-0 flex justify-center items-center bg-black/40 group-hover:opacity-100 opacity-0 duration-200 ease-in-out">
                        <i class='bx bx-photo-album text-5xl text-white p-3 rounded-full'></i>
                    </div>
                    <i v-if="!form.image && !item.pic" class="bx bx-plus font-bold text-3xl dark:text-white"></i>
                    <img v-if="form.image || item.pic" :src="imgTmp || `../../storage/${item.pic}`"
                        class="w-72 h-72 object-cover" alt="">
                </div>

                <InputLabel class="mt-4" for="color" value="Color"/>
                <div class="flex mt-2">
                    <select v-model="form.color" id="color" name="color" @change="selectedColor = $event.target.value" class="rounded-lg border border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                        <template v-for="color in tc">
                            <option v-for="shade in colors[color]" :style="`background: ${shade} !important`" :value="shade">
                                {{ shade }}
                            </option>
                        </template>
                    </select>
                    <div class="w-10 h-10 ml-4" :style="`background-color: ${selectedColor} !important`"></div>
                </div>
            </div>
            <div class="col-span-2 p-6 rounded-lg bg-white dark:bg-zinc-800">
                <form @submit.prevent="form.post(route('items.update', item))" enctype="multipart/form-data">
                    <input ref="newImage" @input="form.image = $event.target.files[0]" @change="showImage" type="file"
                        accept="image/*" hidden>
                    <InputLabel for="categories" value="Category" />
                    <select v-model="form.category_id" id="categories" class="w-full mt-2 border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <option hidden disabled :selected="item.category_id === 69420">Select category</option>
                        <option v-for="category in categories" :value="category.id" :selected="item.category_id === category.id">{{ category.name }}</option>
                    </select>
                    <InputLabel class="mt-4" for="name" value="Name" />
                    <TextInput id="name" type="text" v-model="form.name" class="mt-2 w-full block" />
                    
                    <div class="grid grid-cols-3 gap-1">
                        <div>
                            <InputLabel class="mt-4" for="price" value="Price" />
                            <TextInput id="price" type="number" v-model="form.price" class="mt-2 w-full block" />
                        </div>
                        <div>
                            <InputLabel class="mt-4" for="cost" value="Cost" />
                            <TextInput disabled id="cost" type="number" v-model="costValue" class="mt-2 w-full block disabled:bg-zinc-700" />
                        </div>
                        <div>
                            <InputLabel class="mt-4" for="profit" value="Profit" />
                            <TextInput disabled id="profit" type="number" v-model="profit" class="mt-2 w-full block disabled:bg-zinc-700" />
                        </div>
                    </div>

                    <p class="block font-medium text-sm text-zinc-700 dark:text-zinc-300 mt-4 select-none">Breakdown of
                        cost</p>
                    <div v-for="(ing, index) in form.breakdown" :key="index" class="grid grid-cols-11 gap-2 m-2">
                        <TextInput list="costings" type="text" v-model="ing.name" class="w-full block col-span-5" placeholder="Item" />
                        <TextInput type="number" v-model="ing.cost" class="w-full block col-span-5"
                            placeholder="Cost" />
                        <i @click="removeBreakdown(index)"
                            class="bx bx-x p-2 w-8 h-8 rounded-lg text-white bg-red-500 hover:bg-red-700 duration-200 ease-in-out inline-flex justify-center items-center"></i>
                    </div>
                    <button type="button" class="text-xs px-4 py-1 rounded-lg bg-zinc-400 text-white"
                        @click="addBreakdown">Add</button>
                    <div class="flex justify-end">
                        <button type-="submit"
                            class="rounded-lg px-4 py-2 bg-green-500 hover:bg-green-700 active:bg-green-900 text-white text-sm duration-200 ease-in-out">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="flex justify-end py-8">
            <button @click="showDeleteConfirmationModal = true" type="button"
                class="text-red-500 border border-red-500 px-4 hover:bg-red-500 hover:text-white rounded-lg text-sm">Delete</button>
        </div>
    </div>

    <!-- Delete Modal -->
    <Modal :show="showDeleteConfirmationModal" @close="showDeleteConfirmationModal = false" max-width="md">
        <div class="p-4 dark:text-white">
            <p class="font-bold">Confirmation</p>
            <p>Are you sure you want to delete {{ item.name }}?</p>
            <div class="mt-4 flex justify-end space-x-2">
                <button @click="showDeleteConfirmationModal = false"
                    class="dark:text-white hover:underline">Cancel</button>
                <button @click="$inertia.delete(route('items.destroy', item))"
                    class="bg-red-500 hover:bg-red-700 active:bg-red-900 duration-200 ease-in-out px-3 py-1 text-white rounded-lg">Delete</button>
            </div>
        </div>
    </Modal>

    <!-- Suggestions -->
    <datalist id="costings">
        <option v-for="s in suggestions" :value="s"></option>
    </datalist>
</template>