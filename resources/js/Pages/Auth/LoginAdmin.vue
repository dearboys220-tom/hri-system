<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AuthLayout from '@/Components/User/Layout/AuthLayout.vue'
import {
  UserIcon,
  LockClosedIcon,
  EyeIcon,
  EyeSlashIcon
} from '@heroicons/vue/24/outline'

const showPassword = ref(false)

const form = useForm({
  name: '',
  password: ''
})

const submit = () => {
  form.post('/login-staff')
}
</script>

<template>
  <AuthLayout :centered="true" :showLeft="false">

    <!-- Card -->
    <div
      class="bg-white w-full max-w-md mx-auto
             p-6 sm:p-8
             rounded-2xl shadow-lg border border-gray-100"
    >

      <!-- Header -->
      <div class="mb-6 flex items-start sm:items-center justify-between gap-4">

        <div>
          <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">
            Admin & Staff Login
          </h1>
          <p class="text-xs sm:text-sm text-gray-500 mt-1">
            Masuk ke dashboard internal sistem
          </p>
        </div>

        <img
          src="https://test.hri-check.com/wp-content/uploads/2025/08/cropped-HRI%E3%83%AD%E3%82%B4300.png"
          alt="Company Logo"
          class="h-10 sm:h-14 md:h-16 w-auto object-contain shrink-0"
        />

      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-5">

        <!-- Username -->
        <Transition name="error">
            <div
            v-if="form.errors.name"
            class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl"
            >
            {{ form.errors.name }}
            </div>
        </Transition>
        <div class="relative">
          <UserIcon class="w-5 h-5 absolute left-3 top-3 text-gray-400" />

          <input
            v-model="form.name"
            type="text"
            placeholder="Username"
            required
            autofocus
            class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300
                   focus:ring-2 focus:ring-black focus:outline-none transition"
          />
        </div>

        <!-- Password -->
        <div class="relative">
          <LockClosedIcon class="w-5 h-5 absolute left-3 top-3 text-gray-400" />

          <input
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            placeholder="Password"
            required
            class="w-full pl-10 pr-10 py-3 rounded-xl border border-gray-300
                   focus:ring-2 focus:ring-black focus:outline-none transition"
          />

          <button
            type="button"
            @click="showPassword = !showPassword"
            class="absolute right-3 top-3 text-gray-400 hover:text-black transition"
          >
            <EyeIcon v-if="!showPassword" class="w-5 h-5" />
            <EyeSlashIcon v-else class="w-5 h-5" />
          </button>
        </div>

        <!-- Button -->
        <button
          type="submit"
          :disabled="form.processing"
          class="w-full bg-red-700 text-white py-3 rounded-xl
                 transition hover:bg-red-800
                 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="form.processing">Memproses...</span>
          <span v-else>Login</span>
        </button>

      </form>

    </div>

  </AuthLayout>
</template>
<style>
.error-enter-active,
.error-leave-active {
  transition: all 0.25s ease;
}
.error-enter-from {
  opacity: 0;
  transform: translateY(-6px);
}
.error-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>