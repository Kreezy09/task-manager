<template>
    <div class="space-y-4">
        <!-- Task Filters -->
        <div class="flex flex-wrap gap-2">
            <button
                v-for="status in ['all', 'pending', 'in_progress', 'completed']"
                :key="status"
                @click="filterByStatus(status)"
                :class="[
                    'px-3 py-1 text-sm font-medium rounded-md',
                    currentFilter === status
                        ? 'bg-indigo-100 text-indigo-800'
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                ]"
            >
                {{
                    status === "all"
                        ? "All"
                        : status
                              .replace("_", " ")
                              .replace(/\b\w/g, (l) => l.toUpperCase())
                }}
            </button>
        </div>

        <!-- Tasks List -->
        <div class="space-y-3">
            <div
                v-for="task in filteredTasks"
                :key="task.id"
                class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
            >
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <h3 class="text-lg font-medium text-gray-900">
                                {{ task.title }}
                            </h3>
                            <span
                                :class="[
                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                    getStatusClasses(task.status),
                                ]"
                            >
                                {{
                                    task.status
                                        .replace("_", " ")
                                        .replace(/\b\w/g, (l) =>
                                            l.toUpperCase()
                                        )
                                }}
                            </span>
                        </div>

                        <p class="text-gray-600 mb-3">{{ task.description }}</p>

                        <div
                            class="flex items-center space-x-4 text-sm text-gray-500"
                        >
                            <div class="flex items-center">
                                <svg
                                    class="w-4 h-4 mr-1"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    ></path>
                                </svg>
                                {{ task.user ? task.user.name : "Unassigned" }}
                            </div>
                            <div class="flex items-center">
                                <svg
                                    class="w-4 h-4 mr-1"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    ></path>
                                </svg>
                                {{ formatDate(task.deadline) }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <select
                            v-model="task.status"
                            @change="updateTaskStatus(task)"
                            class="text-sm border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="filteredTasks.length === 0" class="text-center py-8">
                <svg
                    class="mx-auto h-12 w-12 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                    ></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">
                    No tasks found
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    {{
                        currentFilter === "all"
                            ? "Get started by creating a new task."
                            : `No ${currentFilter.replace(
                                  "_",
                                  " "
                              )} tasks found.`
                    }}
                </p>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    name: "TaskList",
    props: {
        tasks: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            currentFilter: "all",
        };
    },
    computed: {
        filteredTasks() {
            if (this.currentFilter === "all") {
                return this.tasks;
            }
            return this.tasks.filter(
                (task) => task.status === this.currentFilter
            );
        },
    },
    methods: {
        filterByStatus(status) {
            this.currentFilter = status;
        },
        async updateTaskStatus(task) {
            try {
                await axios.put(`/api/tasks/${task.id}`, {
                    status: task.status,
                });
                this.$emit("task-updated");
            } catch (error) {
                console.error("Error updating task status:", error);
                alert("Error updating task status. Please try again.");
            }
        },
        getStatusClasses(status) {
            const classes = {
                pending: "bg-yellow-100 text-yellow-800",
                in_progress: "bg-blue-100 text-blue-800",
                completed: "bg-green-100 text-green-800",
            };
            return classes[status] || "bg-gray-100 text-gray-800";
        },
        formatDate(dateString) {
            if (!dateString) return "No deadline";
            const date = new Date(dateString);
            return (
                date.toLocaleDateString() +
                " " +
                date.toLocaleTimeString([], {
                    hour: "2-digit",
                    minute: "2-digit",
                })
            );
        },
    },
};
</script>
