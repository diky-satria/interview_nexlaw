import { createRouter, createWebHistory } from "vue-router";
import TaskIndex from "../views/tasks/TaskIndex.vue";

const routes = [
  {
    path: "/",
    name: "tasks",
    component: TaskIndex,
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
