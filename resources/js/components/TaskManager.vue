<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-gray-900">
                            Task Management System
                        </h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-700"
                            >Welcome, {{ user.name }}</span
                        >
                        <button
                            @click="logout"
                            class="text-sm text-red-600 hover:text-red-800"
                        >
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Admin Dashboard -->
            <div v-if="user.is_admin" class="space-y-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">
                            Admin Dashboard
                        </h2>

                        <!-- User Management -->
                        <div class="mb-8">
                            <h3 class="text-md font-medium text-gray-700 mb-3">
                                User Management
                            </h3>
                            <user-list
                                :users="users"
                                @user-created="loadUsers"
                                @user-updated="loadUsers"
                                @user-deleted="loadUsers"
                            />
                        </div>

                        <!-- Task Creation -->
                        <div class="mb-8">
                            <h3 class="text-md font-medium text-gray-700 mb-3">
                                Create New Task
                            </h3>
                            <task-form
                                :users="users"
                                :user="user"
                                @task-created="loadTasks"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Dashboard -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">
                        My Tasks
                    </h2>
                    <task-list
                        :tasks="userTasks"
                        :user="user"
                        @task-updated="loadTasks"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    name: "TaskManager",
    data() {
        return {
            user: {
                id: null,
                name: "",
                email: "",
                is_admin: false,
            },
            users: [],
            userTasks: [],
            allTasks: [],
        };
    },
    async mounted() {
        await this.loadUser();
        await this.loadUsers();
        await this.loadTasks();
    },
    methods: {
        async loadUser() {
            try {
                const response = await axios.get("/api/user");
                this.user = response.data;
            } catch (error) {
                console.error("Error loading user:", error);
                window.location.href = "/login";
            }
        },
        async loadUsers() {
            if (!this.user.is_admin) return;

            try {
                const response = await axios.get("/api/users");
                this.users = response.data;
            } catch (error) {
                console.error("Error loading users:", error);
            }
        },
        async loadTasks() {
            try {
                if (this.user.is_admin) {
                    const response = await axios.get("/api/tasks");
                    this.allTasks = response.data;
                    this.userTasks = response.data;
                } else {
                    const response = await axios.get("/api/tasks/my");
                    this.userTasks = response.data;
                }
                console.log("Loaded tasks:", this.userTasks);
            } catch (error) {
                console.error("Error loading tasks:", error);
            }
        },
        async logout() {
            try {
                await axios.post("/logout");
                window.location.href = "/login";
            } catch (error) {
                console.error("Error logging out:", error);
            }
        },
    },
};
</script>
