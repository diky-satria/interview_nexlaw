<template>
  <div class="bg-gray-100 min-h-screen py-6">
    <div class="container mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
      <!-- HEADER -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-semibold">Tasks</h1>
        <div class="flex gap-3">
          <a-select
            v-model:value="filters.status"
            placeholder="Status"
            class="w-40"
            allow-clear
          >
            <a-select-option value="open">Open</a-select-option>
            <a-select-option value="pending">Pending</a-select-option>
            <a-select-option value="in_progress">In Progress</a-select-option>
            <a-select-option value="testing">Testing</a-select-option>
            <a-select-option value="completed">Completed</a-select-option>
          </a-select>

          <a-input
            v-model:value="filters.search"
            placeholder="Search..."
            class="w-48"
          >
            <template #suffix> 🔍 </template>
          </a-input>

          <a-button type="primary" @click="openModal">Add Task</a-button>
        </div>
      </div>

      <div v-if="skeleton">
        <a-skeleton active />
      </div>
      <div v-else>
        <!-- TASK LIST -->
        <div class="space-y-4">
          <div
            v-for="task in tasks"
            :key="task.id"
            class="bg-white rounded-xl shadow p-4 flex justify-between items-center"
          >
            <div>
              <h2 class="font-semibold text-gray-800 capitalize">
                {{
                  task?.title?.length > 50
                    ? task?.title?.substring(0, 50) + "..."
                    : task?.title || "-"
                }}
              </h2>
              <p class="text-sm text-gray-500 capitalize">
                {{
                  task?.description?.length > 50
                    ? task?.description?.substring(0, 50) + "..."
                    : task?.description || "-"
                }}
              </p>
            </div>
            <div class="flex items-center gap-4">
              <span
                :class="[
                  'px-3 py-1 text-xs rounded-full font-medium capitalize',
                  statusColors[task.status],
                ]"
              >
                {{ task.status.replace("_", " ") }}
              </span>
              <div class="flex gap-3 text-gray-600">
                <EditOutlined @click="editTask(task)" class="cursor-pointer" />
                <a-popconfirm
                  title="Are you sure delete this task?"
                  ok-text="Yes"
                  cancel-text="No"
                  @confirm="deleteConfirm(task.id)"
                >
                  <DeleteOutlined class="cursor-pointer text-red-500" />
                </a-popconfirm>
              </div>
            </div>
          </div>
        </div>

        <!-- PAGINATION -->
        <div class="flex justify-between mt-6">
          <span class="text-gray-500 text-sm">{{ paginationInfo }}</span>
          <a-pagination
            v-model:current="pagination.page"
            v-model:pageSize="pagination.limit"
            :total="pagination.total_rows"
            :pageSizeOptions="['5', '10', '20', '30', '50', '100']"
            show-size-changer
            @change="handlePageChange"
            @showSizeChange="handleSizeChange"
          />
        </div>
      </div>
    </div>

    <!-- MODAL -->
    <a-modal
      v-model:open="isModalOpen"
      :title="isEditMode ? 'Edit Task' : 'Add Task'"
      @ok="handleSubmit"
      @cancel="closeModal"
      :confirm-loading="loading"
    >
      <a-form layout="vertical">
        <!-- TITLE -->
        <a-form-item label="Title" required>
          <a-input v-model:value="form.title" placeholder="Enter title" />
          <span class="text-sm text-red-500">{{
            errors.title ? errors.title[0] : ""
          }}</span>
        </a-form-item>

        <!-- DESCRIPTION -->
        <a-form-item label="Description">
          <a-textarea
            v-model:value="form.description"
            placeholder="Enter description"
            :rows="3"
          />
          <span class="text-sm text-red-500">{{
            errors.description ? errors.description[0] : ""
          }}</span>
        </a-form-item>

        <!-- STATUS -->
        <a-form-item label="Status" required>
          <a-select v-model:value="form.status" placeholder="Select status">
            <a-select-option value="open">Open</a-select-option>
            <a-select-option value="pending">Pending</a-select-option>
            <a-select-option value="in_progress">In Progress</a-select-option>
            <a-select-option value="testing">Testing</a-select-option>
            <a-select-option value="completed">Completed</a-select-option>
          </a-select>
          <span class="text-sm text-red-500">{{
            errors.status ? errors.status[0] : ""
          }}</span>
        </a-form-item>
      </a-form>
    </a-modal>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { EditOutlined, DeleteOutlined } from "@ant-design/icons-vue";
import { message } from "ant-design-vue";
import axios from "../../services/axios";
import { API_TASKS } from "../../services/api";

// TASK LIST STATE
const filters = ref({
  status: null,
  search: "",
});
const pagination = ref({
  page: 1,
  limit: 5,
  total_rows: 50,
});
const paginationInfo = computed(() => {
  const { page, limit, total_rows } = pagination.value;
  if (total_rows === 0) return "0 task";
  const start = (page - 1) * limit + 1;
  const end = Math.min(page * limit, total_rows);
  return `Show ${start}-${end} of ${total_rows} tasks`;
});
const tasks = ref([]);
const skeleton = ref(true);
const statusColors = ref({
  open: "bg-gray-400 text-white",
  pending: "bg-yellow-400 text-black",
  in_progress: "bg-blue-500 text-white",
  testing: "bg-purple-500 text-white",
  completed: "bg-green-500 text-white",
});

// TASK MODAL STATE
const isEditMode = ref(false);
const selectedId = ref(null);
const isModalOpen = ref(false);
const loading = ref(false);
const form = ref({
  title: "",
  description: "",
  status: "open",
});
const errors = ref([]);

// TASK LIST FUNCTION
const getTasks = async () => {
  skeleton.value = true;
  try {
    const response = await axios.get(API_TASKS, {
      params: {
        status: filters.value.status,
        search: filters.value.search,
        page: pagination.value.page,
        limit: pagination.value.limit,
      },
    });
    tasks.value = response.data.data;
    pagination.value.total_rows = response.data.total_rows;
    skeleton.value = false;
  } catch (error) {
    console.log(error);
    skeleton.value = false;
  }
};
const handlePageChange = (page) => {
  // page = page === 0 ? 1 : page;
  pagination.value.page = page;
  getTasks();
};
const handleSizeChange = (current, size) => {
  pagination.value.page = 1;
  pagination.value.limit = size;
  getTasks();
};

// TASK MODAL FUNCTION
const handleSubmit = async () => {
  try {
    loading.value = true;
    if (isEditMode.value) {
      // UPDATE
      await axios.put(`${API_TASKS}/${selectedId.value}`, {
        title: form.value.title,
        description: form.value.description,
        status: form.value.status,
      });
      message.success("Task edited successfully");
    } else {
      // CREATE
      await axios.post(API_TASKS, {
        title: form.value.title,
        description: form.value.description,
        status: form.value.status,
      });
      message.success("Task added successfully");
    }

    closeModal();
    await getTasks();
  } catch (error) {
    errors.value = error.response.data.errors;
    loading.value = false;
    console.log(error);
  }
};
const openModal = () => {
  isEditMode.value = false;
  selectedId.value = null;

  form.value = {
    title: "",
    description: "",
    status: "open",
  };

  errors.value = [];
  isModalOpen.value = true;
};
const closeModal = () => {
  isModalOpen.value = false;
  errors.value = [];
  loading.value = false;
};
const editTask = (task) => {
  isEditMode.value = true;
  selectedId.value = task.id;

  form.value = {
    title: task.title,
    description: task.description,
    status: task.status,
  };

  errors.value = [];
  isModalOpen.value = true;
};

// TASK DELETE FUNCTION
const deleteConfirm = async (id) => {
  try {
    await axios.delete(`${API_TASKS}/${id}`);

    await getTasks();
    message.success("Task deleted successfully");
  } catch (error) {
    console.log(error);
  }
};

// LIFECYCLE HOOK
watch(
  () => [filters.value.status, filters.value.search],
  () => {
    pagination.value.page = 1;
    getTasks();
  },
);
onMounted(async () => {
  await getTasks();
});
</script>
