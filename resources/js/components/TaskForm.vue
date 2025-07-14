<template>
    <div class="bg-gray-50 p-4 rounded-lg">
        <h4 class="text-sm font-medium text-gray-700 mb-3">Create New Task</h4>
        <form @submit.prevent="createTask" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Title</label
                    >
                    <input
                        v-model="newTask.title"
                        type="text"
                        placeholder="Task title"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        required
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Assign To</label
                    >
                    <select
                        v-model="newTask.user_id"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        required
                    >
                        <option value="">Select a user</option>
                        <option
                            v-for="user in users"
                            :key="user.id"
                            :value="user.id"
                        >
                            {{ user.name }}
                        </option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Description</label
                >
                <textarea
                    v-model="newTask.description"
                    rows="3"
                    placeholder="Task description"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    required
                ></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Deadline</label
                    >
                    <input
                        v-model="newTask.deadline"
                        type="datetime-local"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        required
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Status</label
                    >
                    <select
                        v-model="newTask.status"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        required
                    >
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end">
                <button
                    type="submit"
                    :disabled="isSubmitting"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span v-if="isSubmitting">Creating...</span>
                    <span v-else>Create Task</span>
                </button>
            </div>
        </form>

        <!-- Email Warning Alert -->
        <div
            v-if="emailWarning"
            class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md"
        >
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg
                        class="h-5 w-5 text-yellow-400"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        Email Notification Warning
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>
                            Task was created successfully, but the email
                            notification failed to send. The assigned user may
                            not receive the notification immediately.
                        </p>
                        <p class="mt-1 text-xs">Error: {{ emailError }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    name: "TaskForm",
    props: {
        users: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            newTask: {
                title: "",
                description: "",
                user_id: "",
                deadline: "",
                status: "pending",
            },
            isSubmitting: false,
            emailWarning: false,
            emailError: null,
        };
    },
    methods: {
        async createTask() {
            this.isSubmitting = true;
            this.emailWarning = false;
            this.emailError = null;

            try {
                const response = await axios.post("/api/tasks", this.newTask);

                // Check if email failed to send
                if (response.data.email_sent === false) {
                    this.emailWarning = true;
                    this.emailError =
                        response.data.email_error || "Unknown error";

                    // Show warning but don't treat as error since task was created
                    console.warn(
                        "Task created but email notification failed:",
                        response.data.email_error
                    );
                } else {
                    // Show success message
                    this.showNotification(
                        "Task created successfully!",
                        "success"
                    );
                }

                this.$emit("task-created");
                this.resetForm();
            } catch (error) {
                console.error("Error creating task:", error);

                let errorMessage = "Error creating task. Please try again.";

                if (error.response?.data?.message) {
                    errorMessage = error.response.data.message;
                } else if (error.response?.data?.errors) {
                    // Handle validation errors
                    const errors = error.response.data.errors;
                    errorMessage = Object.values(errors).flat().join(", ");
                }

                this.showNotification(errorMessage, "error");
            } finally {
                this.isSubmitting = false;
            }
        },
        resetForm() {
            this.newTask = {
                title: "",
                description: "",
                user_id: "",
                deadline: "",
                status: "pending",
            };
            this.emailWarning = false;
            this.emailError = null;
        },
        showNotification(message, type = "info") {
            // Simple notification - you could replace this with a proper notification library
            if (type === "error") {
                alert("Error: " + message);
            } else if (type === "success") {
                // For success, we could use a toast notification instead of alert
                console.log("Success:", message);
            }
        },
    },
};
</script>
