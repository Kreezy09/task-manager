<template>
    <div class="space-y-4">
        <!-- Create User Form -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="text-sm font-medium text-gray-700 mb-3">
                Create New User
            </h4>
            <form @submit.prevent="createUser" class="space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <input
                        v-model="newUser.name"
                        type="text"
                        placeholder="Name"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        required
                    />
                    <input
                        v-model="newUser.email"
                        type="email"
                        placeholder="Email"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        required
                    />
                    <input
                        v-model="newUser.password"
                        type="password"
                        placeholder="Password"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        required
                    />
                </div>
                <div class="flex items-center space-x-3">
                    <label class="flex items-center">
                        <input
                            v-model="newUser.is_admin"
                            type="checkbox"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                        <span class="ml-2 text-sm text-gray-700"
                            >Admin User</span
                        >
                    </label>
                    <button
                        type="submit"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Create User
                    </button>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                <li v-for="user in users" :key="user.id" class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div
                                    class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center"
                                >
                                    <span
                                        class="text-sm font-medium text-gray-700"
                                        >{{
                                            user.name.charAt(0).toUpperCase()
                                        }}</span
                                    >
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ user.name }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ user.email }}
                                </div>
                                <div class="text-xs text-gray-400">
                                    {{ user.is_admin ? "Admin" : "User" }}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button
                                @click="editUser(user)"
                                class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                            >
                                Edit
                            </button>
                            <button
                                @click="deleteUser(user.id)"
                                class="text-red-600 hover:text-red-900 text-sm font-medium"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Edit User Modal -->
        <div
            v-if="editingUser"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
        >
            <div
                class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white"
            >
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Edit User
                    </h3>
                    <form @submit.prevent="updateUser" class="space-y-3">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Name</label
                            >
                            <input
                                v-model="editingUser.name"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                required
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Email</label
                            >
                            <input
                                v-model="editingUser.email"
                                type="email"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                required
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >New Password (leave blank to keep
                                current)</label
                            >
                            <input
                                v-model="editingUser.password"
                                type="password"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            />
                        </div>
                        <div class="flex items-center">
                            <input
                                v-model="editingUser.is_admin"
                                type="checkbox"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <label class="ml-2 text-sm text-gray-700"
                                >Admin User</label
                            >
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button
                                type="button"
                                @click="cancelEdit"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    name: "UserList",
    props: {
        users: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            newUser: {
                name: "",
                email: "",
                password: "",
                is_admin: false,
            },
            editingUser: null,
        };
    },
    methods: {
        async createUser() {
            try {
                await axios.post("/api/users", this.newUser);
                this.$emit("user-created");
                this.resetNewUser();
            } catch (error) {
                console.error("Error creating user:", error);
                alert("Error creating user. Please try again.");
            }
        },
        editUser(user) {
            this.editingUser = {
                id: user.id,
                name: user.name,
                email: user.email,
                password: "",
                is_admin: user.is_admin,
            };
        },
        async updateUser() {
            try {
                const data = { ...this.editingUser };
                if (!data.password) {
                    delete data.password;
                }
                await axios.put(`/api/users/${this.editingUser.id}`, data);
                this.$emit("user-updated");
                this.cancelEdit();
            } catch (error) {
                console.error("Error updating user:", error);
                alert("Error updating user. Please try again.");
            }
        },
        async deleteUser(userId) {
            if (!confirm("Are you sure you want to delete this user?")) {
                return;
            }

            try {
                await axios.delete(`/api/users/${userId}`);
                this.$emit("user-deleted");
            } catch (error) {
                console.error("Error deleting user:", error);
                alert("Error deleting user. Please try again.");
            }
        },
        cancelEdit() {
            this.editingUser = null;
        },
        resetNewUser() {
            this.newUser = {
                name: "",
                email: "",
                password: "",
                is_admin: false,
            };
        },
    },
};
</script>
