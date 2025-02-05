<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageContent from '@/Components/PageContent.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useToast } from 'vue-toastification';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    users: Array,
    permissions: Array,
    alerts: Object
})

const toast = useToast()

const newEntry = ref(false)

const selectedUser = ref(null)

const form = useForm({
    full_name: '',
    user_name: '',
    email: '',
    password: '',
    password_confirmation: '',
    permissions: []
})

watch(selectedUser, (newValue, oldValue)=>{
    form.full_name = newValue.full_name
    form.user_name = newValue.user_name
    form.email = newValue.email
    form.permissions = newValue.permissions
    newEntry.value = false;
})

const submit = () => {
    if(newEntry.value) form.post('/users')
    else form.put('/users/' + selectedUser.value.id)
}

watch(()=>props.alerts, () => {
    toast.success(props.alerts.success)
})

const newUser = () => {
    newEntry.value = true
    form.full_name = ''
    form.user_name = ''
    form.email = ''
    form.password = ''
    form.password_confirmation = ''
    form.permissions = []
}

const showDeleteModal = ref(false)

const deleteUser = () => {
    form.delete('/users/' + selectedUser.value.id)
    showDeleteModal.value = false
    newUser()
}

</script>

<template>
<Head title="Users" />

<Modal :show="showDeleteModal">
    <div class="p-8">
        <div class="flex justify-between">
            <h3 class="text-xl">Delete User?</h3>
            <button class="p-4 text-gray-500" @click="showDeleteModal=false" type="button">X</button>
        </div>
        <div class="mb-3">
            Are you sure you want to delete the user
            {{ selectedUser.full_name }}?
        </div>
        <div class="mb-3 flex gap-4">
            <button class="bg-red-700 text-white px-8 py-2" @click="deleteUser">
                Delete
            </button>
            <button class="bg-green-700 text-white px-8 py-2" @click="showDeleteModal=false">
                Cancel
            </button>
        </div>
    </div>
</Modal>

<AuthenticatedLayout>
    <PageContent width="50">
        <div class="flex justify-between mb-3">
            <h2 class="text-2xl mb-2">Users</h2>
            <button class="bg-green-700 text-white px-8 py-2 rounded shadow" @click="newUser">
                <font-awesome-icon icon="fa-solid fa-user-plus"></font-awesome-icon>
                New User
            </button>
        </div>
        <hr>
        <div class="flex gap-6">
            <div class="border p-4 mt-3 shadow flex flex-col justify-stretched">
                <div v-for="user in users" :key="user.id" class="p-4 border-b border-gray-400 hover:bg-gray-100 cursor-pointer px-8" @click="selectedUser=user">
                    {{ user.full_name }}
                </div>
            </div>
            <div class="border p-4 mt-3 flex-1 shadow" v-if="newEntry || selectedUser!=null">
                <h3>User Entry Form</h3>
                <form @submit.prevent="submit">
                    <div class="pb-8">
                        <div class="my-4">
                            <label for="full_name" class="block text-sm font-medium text-green-900 dark:text-gray-400">Full Name</label>
                            <input v-model="form.full_name" type="text" id="full_name" class="block w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm bg-gray-100 dark:bg-gray-600" />
                            <i v-if="form.errors.full_name" class="text-red-600 text-sm">{{ form.errors.full_name }}</i>
                        </div>
                        <div class="mb-4">
                            <label for="user_name" class="block text-sm font-medium text-green-900 dark:text-gray-400">User Name</label>
                            <input v-model="form.user_name" type="text" id="user_name" class="block w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm bg-gray-100 dark:bg-gray-600" />
                            <i v-if="form.errors.user_name" class="text-red-600 text-sm">{{ form.errors.user_name }}</i>
                        </div>
                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-green-900 dark:text-gray-400">Email</label>
                            <input v-model="form.email" type="text" id="email" class="block w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm bg-gray-100 dark:bg-gray-600" />
                            <i v-if="form.errors.email" class="text-red-600 text-sm">{{ form.errors.email }}</i>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="block text-sm font-medium text-green-900 dark:text-gray-400">Password</label>
                            <input v-model="form.password" type="password" id="password" class="block w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm bg-gray-100 dark:bg-gray-600" />
                            <i v-if="form.errors.password" class="text-red-600 text-sm">{{ form.errors.password }}</i>
                        </div>
                        <div class="mb-4" v-if="newEntry">
                            <label for="password_confirmation" class="block text-sm font-medium text-green-900 dark:text-gray-400">Confirm Password</label>
                            <input v-model="form.password_confirmation" type="password" id="password_confirmation" class="block w-full border-gray-300 rounded-md focus:border-green-500 focus:ring-green-500 sm:text-sm bg-gray-100 dark:bg-gray-600" />
                            <i v-if="form.errors.password_confirmation" class="text-red-600 text-sm">{{ form.errors.password_confirmation }}</i>
                        </div>
                        <div class="mb-4">
                            <h3>Extra Permissions</h3>
                            <div>
                                <label for="permission" v-for="perm in permissions" :key="perm.id" class="pe-4">
                                    <input type="checkbox" :value="perm.name" v-model="form.permissions" >
                                    {{ perm.name }}
                                </label>
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <button class="bg-green-700 text-white px-8 py-3 rounded" type="submit">
                                Save Changes
                            </button>
                            <button class="bg-red-700 text-white px-4 rounded" type="button" @click="showDeleteModal=true" title="Delete this user.">
                                <font-awesome-icon icon="fa-solid fa-trash-can"></font-awesome-icon>
                            </button>

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </PageContent>
</AuthenticatedLayout>
</template>
