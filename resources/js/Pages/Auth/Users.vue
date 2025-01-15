<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { ref } from 'vue';

const props = defineProps({
    users: Object,
    errors: Object
})

const newUserForm = useForm({
    name: '',
    email: '',
    password: '',
})

const submitNewUserForm = () => {
    newUserForm.post(route('users.store'), {
        onSuccess: () => {
            closeModal()
        }
    })
}

const editUser = (user) => {
    editUserForm.name = user.name
    editUserForm.email = user.email
    editUserForm.id = user.id

    showEditUserModal.value = true
}

const editUserForm = useForm({
    name: '',
    email: '',
    password: '',
    id: '',
})

const submitEditUserForm = () => {
    editUserForm.put(route('users.update', editUserForm.id), {
        onSuccess: () => {
            closeModal()
        }
    })
}

const userToDelete = ref(null)
const deleteUser = (user) => {
    userToDelete.value = user
    showDeleteUserModal.value = true
}

const showNewUserModal = ref(false)
const showEditUserModal = ref(false)
const showDeleteUserModal = ref(false)

const closeModal = () => {
    showNewUserModal.value = false
    showEditUserModal.value = false
    showDeleteUserModal.value = false
    newUserForm.reset()
    editUserForm.reset()
    userToDelete.value = null
}
</script>

<template>

    <Head>
        <title>Users</title>
    </Head>

    <div class="max-w-md mx-auto my-8 dark:text-zinc-100">
        <div class="flex justify-end mb-4">
            <button @click="showNewUserModal = true"
                class="py-1 px-2 text-white bg-green-500 hover:bg-green-700 active:bg-green-800 duration-200 ease-in-out rounded-lg text-xs">New
                user</button>
        </div>

        <div v-for="user in users"
            class="dark:bg-zinc-800 bg-white p-4 shadow-md group dark:hover:bg-zinc-700 duration-200 ease-in-out flex justify-between items-center mb-1">
            <p>{{ user.name }}</p>
            <div class="space-x-2">
                <i @click="editUser(user)"
                    class="bx bx-edit invisible group-hover:visible inline-flex justify-center items-center h-5 w-5 p-4 rounded-full cursor-pointer hover:bg-blue-500"></i>
                <i v-if="user.id !== 1" @click="deleteUser(user)"
                    class="bx bx-trash invisible group-hover:visible inline-flex justify-center items-center h-5 w-5 p-4 rounded-full cursor-pointer hover:bg-red-500"></i>
            </div>
        </div>
    </div>

    <!-- New User Modal -->
    <Modal max-width="sm" :show="showNewUserModal" @close="closeModal">
        <div class="bg-white dark:bg-zinc-800 p-4 shadow-lg dark:text-white">
            <p class="font-bold mb-4">NEW USER</p>

            <InputLabel for="newUserEmail" value="Email" />
            <TextInput @keyup.enter="submitNewUserForm" class="w-full mb-2" v-model="newUserForm.email" name="newUserEmail" id="newUserEmail"
                type="email" />

            <InputLabel for="newUserName" value="Name" />
            <TextInput @keyup.enter="submitNewUserForm" class="w-full mb-2" v-model="newUserForm.name" name="newUserName" id="newUserName" type="text" />

            <InputLabel for="newUserPassword" value="Password" />
            <TextInput @keyup.enter="submitNewUserForm" class="w-full" v-model="newUserForm.password" name="newUserPassword" id="newUserPassword"
                type="password" />

            <button @click="submitNewUserForm"
                class="flex justify-center mt-4 w-full py-1 text-white text-sm bg-blue-500 hover:bg-blue-700 active:bg-blue-800 duration-200 ease-in-out disabled:opacity-25"
                :disabled="newUserForm.processing">
                Save
            </button>
        </div>
    </Modal>

    <!-- Edit User Modal -->
    <Modal max-width="sm" :show="showEditUserModal" @close="closeModal">
        <div class="bg-white dark:bg-zinc-800 p-4 shadow-lg dark:text-white">
            <p class="font-bold mb-4">EDIT USER</p>

            <InputLabel for="editUserEmail" value="Email" />
            <TextInput @keyup.enter="submitEditUserForm" class="w-full mb-2" v-model="editUserForm.email" name="editUserEmail" id="editUserEmail"
                type="email" />

            <InputLabel for="editUserName" value="Name" />
            <TextInput @keyup.enter="submitEditUserForm" class="w-full mb-2" v-model="editUserForm.name" name="editUserName" id="editUserName"
                type="text" />

            <InputLabel for="editUserPassword" value="Password" />
            <TextInput @keyup.enter="submitEditUserForm" class="w-full" v-model="editUserForm.password" name="editUserPassword" id="editUserPassword"
                type="password" />

            <button @click="submitEditUserForm"
                class="flex justify-center mt-4 w-full py-1 text-white text-sm bg-blue-500 hover:bg-blue-700 active:bg-blue-800 duration-200 ease-in-out disabled:opacity-25"
                :disabled="editUserForm.processing">
                Save
            </button>
        </div>
    </Modal>

    <!-- Delete User Modal -->
    <Modal :show="showDeleteUserModal" @close="closeModal" max-width="sm">
        <div class="bg-white dark:bg-zinc-800 p-4 dark:text-zinc-100">
            <p class="text-sm font-bold mb-2">Confirmation</p>
            <p>Are you sure you want to delete <span class="font-bold">{{ userToDelete?.name }}</span>?</p>

            <div class="flex justify-end mt-4 space-x-4">
                <button class="hover:underline">Cancel</button>
                <button @click="$inertia.delete(route('users.destroy', userToDelete?.id), {
                onSuccess: () => closeModal(),
            })"
                    class="px-4 py-2 text-white text-sm bg-red-500 hover:bg-red-700 active:bg-red-800 duration-200 ease-in-out">Delete</button>
            </div>
        </div>
    </Modal>
</template>