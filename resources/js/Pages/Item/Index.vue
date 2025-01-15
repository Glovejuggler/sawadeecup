<script setup>
import { Link, Head, useForm } from '@inertiajs/vue3';
import { ref, nextTick } from 'vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';

const newCategoryModal = ref(false);
const newCatInput = ref(null)

const addNewCategory = () => {
    newCategoryModal.value = true

    nextTick(() => newCatInput.value.focus())
}

const closeNewCategory = () => {
    newCategoryModal.value = false

    newCatForm.reset()
}

const submitCategory = () => {
    newCatForm.post(route('categories.store'), {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => closeNewCategory,
        onError: () => newCatInput.value.focus(),
        onFinish: () => newCatForm.reset()
    })
}

const editCategoryModal = ref(false)
const editCatInput = ref(null)

const editCategory = (category) => {
    editCatForm.name = category.name
    editCatForm.id = category.id
    editCategoryModal.value = true

    nextTick(() => editCatInput.value.focus())
}

const closeEditCategory = () => {
    editCategoryModal.value = false

    editCatForm.reset()
}

const submitEditCategory = () => {
    editCatForm.put(route('categories.update', editCatForm.id), {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => closeEditCategory,
        onError: () => editCatInput.value.focus(),
        onFinish: () => editCatForm.reset()
    })
}

const deleteModal = ref(false)
const categoryToDelete = ref(null)
const deleteCategory = (category) => {
    categoryToDelete.value = category
    deleteModal.value = true
}

const closeDeleteModal = () => {
    deleteModal.value = false
    categoryToDelete.value = null
}

const props = defineProps({
    items: Object,
    categories: Object,
    category: Object,
})

const newCatForm = useForm({
    name: ''
})

const editCatForm = useForm({
    name: '',
    id: '',
})
</script>

<template>

    <Head>
        <title>
            Products
        </title>
    </Head>

    <div class="max-w-7xl lg:flex mx-auto mt-8 px-6 lg:px-8">
        <div class="w-full lg:w-1/6 bg-white dark:bg-zinc-900 py-4 rounded-lg h-min border dark:border-zinc-700 mb-6 text-sm">
            <div class="flex justify-between items-center px-4 dark:text-white">
                <span>Categories</span>
                <i @click="addNewCategory"
                    class='bx bx-plus rounded-full hover:bg-black/20 dark:hover:bg-white/20 w-8 h-8 inline-flex justify-center items-center duration-200 ease-in-out'></i>
            </div>
            <hr class="border-zinc-600 mt-1">
            <div v-for="category in categories" @click.stop="$inertia.get(route('items.index', category))"
                :class="{ 'bg-blue-500 hover:bg-blue-500 font-bold text-white': $page.url.replaceAll('%20', ' ') === `/items/${category.name}` }"
                class="dark:text-white px-4 py-2 duration-200 ease-in-out flex justify-between group cursor-pointer">
                    <span>{{ category.name }}</span>
                    <div class="invisible group-hover:visible space-x-2">
                        <i class="bx bx-edit opacity-70 hover:opacity-100" @click.stop="editCategory(category)"></i>
                        <i class="bx bx-trash opacity-70 hover:opacity-100" @click.stop="deleteCategory(category)"></i>
                    </div>
            </div>
        </div>
        <div class="w-full md:w-5/6 md:px-4 dark:text-white">
            <div v-if="category" class="flex items-center mb-4">
                <span class="font-semibold mr-8">Products</span>
                <i @click="$inertia.get(route('items.create'), { category: category.id })"
                    class="bx bx-plus rounded-full hover:bg-black/20 dark:hover:bg-white/20 w-8 h-8 inline-flex justify-center items-center duration-200 ease-in-out"></i>
            </div>
            <div class="grid grid-cols-3 lg:grid-cols-5 gap-2">
                <div @click="$inertia.get(route('items.show', item.id))" v-for="item in items"
                    class="flex flex-col rounded-lg overflow-hidden border dark:border-zinc-700 relative">
                    <div class="w-full aspect-square" :style="`background: ${item.color ?? 'rgb(56, 56, 65)'} !important`">
                        <img class="object-fit" v-if="item.pic" :src="`../storage/${item.pic}`">
                    </div>
                    <div class="bg-black/50 p-2 inset-x-0 bottom-0 absolute text-sm text-white">{{ item.name }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Category Modal -->
    <Modal :show="newCategoryModal" @close="closeNewCategory">
        <div class="p-6">
            <p class="dark:text-white font-semibold">Add Category</p>
            <TextInput ref="newCatInput" class="mt-2 block w-full" type="text" v-model="newCatForm.name"
                placeholder="Category" @keyup.enter="submitCategory" />
        </div>
    </Modal>

    <!-- Edit Category Modal -->
    <Modal :show="editCategoryModal" @close="closeEditCategory">
        <div class="p-6">
            <p class="dark:text-white font-semibold">Edit Category</p>
            <TextInput ref="editCatInput" class="mt-2 block w-full" type="text" v-model="editCatForm.name"
                placeholder="Category" @keyup.enter="submitEditCategory" />
        </div>
    </Modal>

    <!-- Delete Category Modal -->
    <Modal :show="deleteModal" @close="closeDeleteModal">
        <div class="p-6 dark:text-white">
            <p class="font-semibold">Confirmation</p>
            <p class="mt-2">Are you sure you want to delete this category: {{ categoryToDelete.name }}?</p>
            <p class="sm italic text-red-500">This action cannot be undone</p>
            <div class="mt-4 flex justify-end space-x-2">
                <button @click="closeDeleteModal" class="hover:underline">Cancel</button>
                <button @click="$inertia.delete(route('categories.destroy', categoryToDelete), {
                    preserveScroll: true,
                    preserveState: false,
                    onSuccess: () => closeDeleteModal,
                })" class="px-4 py-2 text-sm text-white bg-red-500 hover:bg-red-700 active:bg-red-900 duration-200 ease-in-out">Delete</button>
            </div>
        </div>
    </Modal>
</template>