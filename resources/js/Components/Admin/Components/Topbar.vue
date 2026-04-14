<script setup>
import {
  MagnifyingGlassIcon,
  ArrowRightOnRectangleIcon,
  BookOpenIcon,
  ClipboardDocumentListIcon,
  CalendarDaysIcon,
  UserCircleIcon,
} from '@heroicons/vue/24/outline'
import Button from './Button.vue'
import { useForm, usePage, Link } from '@inertiajs/vue3'

defineProps({
  title: {
    type: String,
    default: 'Tim'
  },
  subtitle: {
    type: String,
    default: 'Penyelidikan'
  }
})

const page     = usePage()
const userName = page.props.auth?.user?.name ?? 'Staff'

const logoutForm = useForm({})
const logout = () => {
  logoutForm.post(route('staff.logout'))
}
</script>

<template>
  <header
    class="sticky top-0 z-40
           bg-gradient-to-r from-admin-primary-700 to-admin-primary-900
           px-4 sm:px-6 md:px-8 py-5 shadow-md"
  >
    <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-5">

      <!-- LEFT -->
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-100 flex items-center gap-3">
          <MagnifyingGlassIcon class="w-5 h-5 sm:w-6 sm:h-6 text-slate-200" />
          {{ title }}
        </h1>
        <p class="text-sm text-slate-300 mt-1">
          {{ subtitle }}
        </p>
      </div>

      <!-- RIGHT -->
      <div class="flex flex-col items-end gap-3 w-full md:w-auto">

        <!-- Penanggung Jawab -->
        <div class="text-sm text-slate-200 text-right w-full md:w-auto order-2 md:order-1">
          Penanggung Jawab:
          <span class="font-semibold text-white">{{ userName }}</span>
        </div>

        <!-- Buttons -->
        <div class="flex w-full md:w-auto gap-2 flex-wrap order-1 md:order-2 justify-end">

          <!-- ★ マイページ -->
          <Link :href="route('staff.mypage')">
            <Button variant="secondary" size="sm" class="flex-1 md:flex-none">
              <UserCircleIcon class="w-4 h-4" />
              Halaman Saya
            </Button>
          </Link>
          
          <!-- ★ マイタスク -->
          <Link :href="route('staff.tasks.index')">
            <Button variant="secondary" size="sm" class="flex-1 md:flex-none">
              <ClipboardDocumentListIcon class="w-4 h-4" />
              Tugas Saya
            </Button>
          </Link>

          <!-- ★ 欠勤申請 -->
          <Link :href="route('staff.absence.create')">
            <Button variant="secondary" size="sm" class="flex-1 md:flex-none">
              <CalendarDaysIcon class="w-4 h-4" />
              Izin
            </Button>
          </Link>

          <!-- ログアウト -->
          <Button
            variant="danger"
            size="sm"
            class="flex-1 md:flex-none"
            @click="logout"
          >
            <ArrowRightOnRectangleIcon class="w-4 h-4" />
            Keluar
          </Button>

        </div>
      </div>
    </div>
  </header>
</template>