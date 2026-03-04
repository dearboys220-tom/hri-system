<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AuthLayout from '@/Components/User/Layout/AuthLayout.vue'
import {
  BuildingOffice2Icon,
  UserIcon,
  IdentificationIcon,
  EnvelopeIcon,
  LockClosedIcon,
  EyeIcon,
  EyeSlashIcon,
  ArrowUpTrayIcon
} from '@heroicons/vue/24/outline'

const showPassword = ref(false)
const showConfirmPassword = ref(false)
const agreementError = ref(false)

const form = useForm({
  company_name: '',
  nib: '',
  pic_name: '',
  pic_position: '',
  pic_phone: '',
  company_email: '',
  password: '',
  password_confirmation: '',
  akta_pendirian: null,
})

const canSubmit = computed(() => {
  return form.agree && !form.processing
})

const submit = () => {

  if (!form.agree) {
    agreementError.value = true
    return
  }

  agreementError.value = false

  form.post('/register/company', {
    forceFormData: true
  })
}

const fileName = ref('Belum ada file dipilih')

const handleFileChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    form.akta_pendirian = file
    fileName.value = file.name
  }
}

const confirmPasswordError = computed(() => {

  if (form.errors.password) {
    return form.errors.password
  }

  if (
    form.password_confirmation &&
    form.password !== form.password_confirmation
  ) {
    return 'Konfirmasi password tidak cocok.'
  }

  return null
})

/* ================= LEFT CONTENT ================= */
const leftContent = computed(() => ({
  title: 'Daftarkan Perusahaan Anda',
  description:
    'Lengkapi data perusahaan untuk memulai proses verifikasi dan membangun sistem rekrutmen yang aman dan terpercaya.',
  image:
    'https://images.unsplash.com/photo-1522202176988-66273c2fd55f',
  gradient: 'from-black/80 via-black/60 to-black/40'
}))
</script>
<template>
  <AuthLayout
    :centered="false"
    :title="leftContent.title"
    :description="leftContent.description"
    :image="leftContent.image"
    :gradient="leftContent.gradient"
  >
    <div class="flex flex-col h-[calc(100vh-4rem)]">

        <!-- HEADER (Stay) -->
        <div class="pb-6">
            <h2 class="text-2xl font-semibold text-gray-800">
                Daftar Perusahaan
            </h2>
            
            <Link
            :href="route('login')" 
            class="text-sm text-gray-500 hover:text-black transition"
            >
            ← Kembali ke Login
            </Link>
        </div>

        <div class="flex-1 overflow-y-auto p-5 pl-1">
        <form @submit.prevent="submit" class="space-y-5">
           <div class="relative">
            <BuildingOffice2Icon class="w-5 h-5 absolute left-3 top-3 text-gray-400" />
                <input
                    v-model="form.company_name"
                    type="text"
                    placeholder="Nama Perusahaan"
                    class="w-full pl-10 pr-4 py-3 rounded-xl border focus:ring-2 focus:outline-none bg-white"
                    :class="form.errors.company_name 
                        ? 'border-red-500 focus:ring-red-500' 
                        : 'border-gray-300 focus:ring-black'"
                />

                <p v-if="form.errors.company_name" class="text-sm text-red-500 mt-1">
                    {{ form.errors.company_name }}
                </p>
            </div>

            <div class="relative">
                <IdentificationIcon class="w-5 h-5 absolute left-3 top-3 text-gray-400" />
                <input
                    v-model="form.nib"
                    type="number"
                    placeholder="NIB"
                    class="w-full pl-10 pr-4 py-3 rounded-xl border focus:ring-2 focus:outline-none bg-white"
                    :class="form.errors.nib 
                        ? 'border-red-500 focus:ring-red-500' 
                        : 'border-gray-300 focus:ring-black'"
                />
                <p v-if="form.errors.nib" class="text-sm text-red-500 mt-1">
                    {{ form.errors.nib }}
                </p>
            </div>

            <div class="relative">
                <UserIcon class="w-5 h-5 absolute left-3 top-3 text-gray-400" />
                <input
                    v-model="form.pic_name"
                    type="text"
                    placeholder="Nama Penanggung Jawab"
                    class="w-full pl-10 pr-4 py-3 rounded-xl border focus:ring-2 focus:outline-none bg-white"
                    :class="form.errors.pic_name 
                        ? 'border-red-500 focus:ring-red-500' 
                        : 'border-gray-300 focus:ring-black'"
                />
                <p v-if="form.errors.pic_name" class="text-sm text-red-500 mt-1">
                    {{ form.errors.pic_name }}
                </p>
            </div>

            <div class="relative">
                <UserIcon class="w-5 h-5 absolute left-3 top-3 text-gray-400" />
                <input
                    v-model="form.pic_position"
                    type="text"
                    placeholder="Jabatan"
                    class="w-full pl-10 pr-4 py-3 rounded-xl border focus:ring-2 focus:outline-none bg-white"
                    :class="form.errors.pic_position 
                        ? 'border-red-500 focus:ring-red-500' 
                        : 'border-gray-300 focus:ring-black'"
                />
                <p v-if="form.errors.pic_position" class="text-sm text-red-500 mt-1">
                    {{ form.errors.pic_position }}
                </p>
            </div>

            <div class="relative">
                <UserIcon class="w-5 h-5 absolute left-3 top-3 text-gray-400" />
                <input
                    v-model="form.pic_phone"
                    type="number"
                    placeholder="Nomor Penanggung Jawab"
                    class="w-full pl-10 pr-4 py-3 rounded-xl border focus:ring-2 focus:outline-none bg-white"
                    :class="form.errors.pic_phone 
                        ? 'border-red-500 focus:ring-red-500' 
                        : 'border-gray-300 focus:ring-black'"
                />
                <p v-if="form.errors.pic_phone" class="text-sm text-red-500 mt-1">
                    {{ form.errors.pic_phone }}
                </p>
            </div>

            <div class="relative">
                <EnvelopeIcon class="w-5 h-5 absolute left-3 top-3 text-gray-400" />
                <input
                    v-model="form.company_email"
                    type="email"
                    placeholder="Email Perusahaan"
                    class="w-full pl-10 pr-4 py-3 rounded-xl border focus:ring-2 focus:outline-none bg-white"
                    :class="form.errors.company_email 
                        ? 'border-red-500 focus:ring-red-500' 
                        : 'border-gray-300 focus:ring-black'"
                />
                <p v-if="form.errors.company_email" class="text-sm text-red-500 mt-1">
                    {{ form.errors.company_email }}
                </p>
            </div>

            <div class="relative">
                <LockClosedIcon class="w-5 h-5 absolute left-3 top-3 text-gray-400" />
                <input
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    placeholder="Kata Sandi"
                    class="w-full pl-10 pr-10 py-3 rounded-xl border focus:ring-2 focus:outline-none bg-white"
                    :class="form.errors.password 
                        ? 'border-red-500 focus:ring-red-500' 
                        : 'border-gray-300 focus:ring-black'"
                />
                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute right-3 top-3 text-gray-400"
                >
                    <EyeIcon v-if="!showPassword" class="w-5 h-5" />
                    <EyeSlashIcon v-else class="w-5 h-5" />
                </button>
            </div>
            <p v-if="form.errors.password" class="text-sm text-red-500 mt-1">
                {{ form.errors.password }}
            </p>

            <div class="relative">
                <LockClosedIcon class="w-5 h-5 absolute left-3 top-3 text-gray-400" />
                <input
                    v-model="form.password_confirmation"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    placeholder="Konfirmasi Kata Sandi"
                    class="w-full pl-10 pr-10 py-3 rounded-xl border focus:ring-2 focus:outline-none bg-white"
                    :class="confirmPasswordError 
                        ? 'border-red-500 focus:ring-red-500' 
                        : 'border-gray-300 focus:ring-black'"
                />
                <button
                    type="button"
                    @click="showConfirmPassword = !showConfirmPassword"
                    class="absolute right-3 top-3 text-gray-400"
                >
                    <EyeIcon v-if="!showConfirmPassword" class="w-5 h-5" />
                    <EyeSlashIcon v-else class="w-5 h-5" />
                </button>
            </div>
            <p v-if="confirmPasswordError" class="text-sm text-red-500 mt-1">
                {{ confirmPasswordError }}
            </p>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Surat Kuasa / Penunjukan
                </label>

                <label
                    class="flex flex-col items-center justify-center w-full px-6 py-8 border-2 border-dashed rounded-xl cursor-pointer transition bg-gray-50"
                    :class="form.errors.akta_pendirian
                        ? 'border-red-500 bg-red-50'
                        : 'border-gray-300 hover:border-black'"
                >
                    <div class="text-center space-y-2">
                        <ArrowUpTrayIcon 
                            class="w-8 h-8 mx-auto"
                            :class="form.errors.akta_pendirian ? 'text-red-500' : 'text-gray-400'"
                        />
                        <p class="text-sm" :class="form.errors.akta_pendirian ? 'text-red-600' : 'text-gray-600'">
                            Klik untuk upload atau drag file ke sini
                        </p>
                        <p class="text-xs text-gray-400">
                            PDF, JPG, PNG (Max 2MB)
                        </p>
                        <p class="text-sm font-medium" :class="form.errors.akta_pendirian ? 'text-red-600' : 'text-black'">
                            {{ fileName }}
                        </p>
                    </div>

                    <input
                    type="file"
                    class="hidden"
                    @change="handleFileChange"
                    accept=".pdf,.jpg,.jpeg,.png"
                    />
                </label>
                <p v-if="form.errors.akta_pendirian" class="text-sm text-red-500 mt-2">
                    {{ form.errors.akta_pendirian }}
                </p>
            </div>

        </form>
        </div>
        <div class="pt-4 border-t bg-white shrink-0">

            <!-- AGREEMENT -->
            <div class="flex items-start gap-2 text-sm mb-2">
                <input 
                    type="checkbox" 
                    v-model="form.agree" 
                    class="mt-1"
                />

                <span>
                    Saya menyetujui
                    <Link href="/terms" class="text-black font-medium hover:underline">
                        Syarat dan Ketentuan
                    </Link>
                </span>
            </div>

            <!-- ERROR MESSAGE -->
            <p v-if="agreementError" class="text-sm text-red-500 mb-3">
                Anda harus menyetujui Syarat dan Ketentuan.
            </p>

            <!-- SUBMIT BUTTON -->
            <button
                type="button"
                @click="submit"
                :disabled="!canSubmit"
                class="w-full flex items-center justify-center gap-2 bg-red-700 text-white py-3 rounded-xl transition disabled:opacity-50 disabled:cursor-not-allowed hover:bg-red-800"
            >
                <!-- Spinner -->
                <svg
                    v-if="form.processing"
                    class="w-5 h-5 animate-spin"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    ></circle>
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8v8H4z"
                    ></path>
                </svg>

                <span>
                    {{ form.processing ? 'Memproses...' : 'Daftar Perusahaan' }}
                </span>
            </button>

        </div>

    </div>

  </AuthLayout>
</template>
