import "./bootstrap";
import "../css/app.css";

import { createApp } from "vue";

// Import Vue components
import TaskManager from "./components/TaskManager.vue";
import UserList from "./components/UserList.vue";
import TaskForm from "./components/TaskForm.vue";
import TaskList from "./components/TaskList.vue";

// Create Vue app
const app = createApp({});

// Register components
app.component("task-manager", TaskManager);
app.component("user-list", UserList);
app.component("task-form", TaskForm);
app.component("task-list", TaskList);

// Mount the app
app.mount("#app");
