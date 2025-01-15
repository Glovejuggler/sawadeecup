<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref, computed } from 'vue';
import colors from 'tailwindcss/colors';

const props = defineProps({
    suggestions: Object
})

const tc = Object.keys(colors)
    .filter(key => typeof colors[key] === 'object')

const params = new URLSearchParams(window.location.search)

const form = useForm({
    category_id: params.get('category'),
    image: '',
    name: '',
    price: '',
    color: '',
    breakdown: []
})

const costValue = computed(() => {
    return form.breakdown.reduce((acc, item) => acc + item.cost, 0)
})
const profit = computed(() => form.price - costValue.value)

const selectedColor = ref('')
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
</script>

<template>

    <Head>
        <title>
            Add new item
        </title>
    </Head>

    <div class="max-w-screen-lg mx-auto mt-8">
        <p class="dark:text-white mb-8">New item</p>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <div @click="newImage.click()"
                    class="h-72 w-72 rounded-lg overflow-hidden bg-white dark:bg-zinc-700 flex justify-center items-center">
                    <i v-if="!form.image" class="bx bx-plus font-bold text-3xl dark:text-white"></i>
                    <img v-if="form.image" :src="imgTmp" class="w-72 h-72 object-cover" alt="">
                </div>

                <InputLabel class="mt-4" for="color" value="Color"/>
                <div class="flex mt-2">
                    <select v-model="form.color" id="color" name="color" @change="selectedColor = $event.target.value" class="rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 dark:text-white">
                        <template v-for="color in tc">
                            <option v-for="shade in colors[color]" :style="`background: ${shade} !important`" :value="shade">
                                {{ shade }}
                            </option>
                        </template>
                    </select>
                    <div class="w-10 h-10 ml-4" :style="`background-color: ${selectedColor} !important`"></div>
                </div>
            </div>
            <div class="col-span-2 p-6 rounded-lg bg-white dark:bg-zinc-800 h-min">
                <form @submit.prevent="form.post(route('items.store'))">
                    <input ref="newImage" @input="form.image = $event.target.files[0]" @change="showImage" type="file"
                        accept="image/*" hidden>
                    <InputLabel for="name" value="Name" />
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
    </div>

    <!-- Suggestions -->
    <datalist id="costings">
        <option v-for="s in suggestions" :value="s"></option>
    </datalist>
</template>