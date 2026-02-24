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

const form = useForm({
  company_name: '',
  nib: '',
  person_name: '',
  position: '',
  person_phone: '',
  company_email: '',
  password: '',
  password_confirmation: '',
  power_letter: null,
  agree: false
})

const submit = () => {
  form.post('/register/company')
}

const fileName = ref('Belum ada file dipilih')

const handleFileChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    form.power_letter = file
    fileName.value = file.name
  }
}

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
            href="/login"
            class="text-sm text-gray-500 hover:text-black transition"
            >
            ← Kembali ke Login
            </Link>
        </div>

        <!-- FORM SCROLL AREA -->
        <div class="flex-1 overflow-y-auto p-5 pl-1">
        <form @submit.prevent="submit" class="space-y-5">

            <!-- Back -->

           <div class="relative">
            <BuildingOffice2Icon class="w-5 h-5 absolute left-3 top-3 text-gray-400" />
            <input
                v-model="form.company_name"
                type="text"
                placeholder="Nama Perusahaan"
                class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-black focus:outline-none bg-white"
            />
                </div>


            <!-- NIB -->
            <div class="relative">
                <IdentificationIcon class="w-5 h-5 absolute left-3 top-3 text-gray-400" />
                <input
                    v-model="form.nib"
                    type="text"
                    placeholder="NIB"
                    class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-black focus:outline-none bg-white"
                />
            </div>

            <!-- Nama PJ -->
            <div class="relative">
                <UserIcon class="w-5 h-5 absolute left-3 top-3 text-gray-400" />
                <input
                    v-model="form.person_name"
                    type="text"
                    placeholder="Nama Penanggung Jawab"
                    class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-black focus:outline-none bg-white"
                />
            </div>

            <!-- Jabatan -->
            <div class="relative">
                <UserIcon class="w-5 h-5 absolute left-3 top-3 text-gray-400" />
                <input
                    v-model="form.position"
                    type="text"
                    placeholder="Jabatan"
                    class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-black focus:outline-none bg-white"
                />
            </div>

            <div class="relative">
                <UserIcon class="w-5 h-5 absolute left-3 top-3 text-gray-400" />
                <input
                    v-model="form.person_phone"
                    type="text"
                    placeholder="Nomor Penanggung Jawab"
                    class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-black focus:outline-none bg-white"
                />
            </div>

            <!-- Email -->
            <div class="relative">
                <EnvelopeIcon class="w-5 h-5 absolute left-3 top-3 text-gray-400" />
                <input
                    v-model="form.company_email"
                    type="email"
                    placeholder="Email Perusahaan"
                    class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-black focus:outline-none bg-white"
                />
            </div>

            <!-- Password -->
            <div class="relative">
                <LockClosedIcon class="w-5 h-5 absolute left-3 top-3 text-gray-400" />
                <input
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    placeholder="Kata Sandi"
                    class="w-full pl-10 pr-10 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-black focus:outline-none bg-white"
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
            <!-- Confirm -->
            <div class="relative">
                <LockClosedIcon class="w-5 h-5 absolute left-3 top-3 text-gray-400" />
                <input
                    v-model="form.password_confirmation"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    placeholder="Konfirmasi Kata Sandi"
                    class="w-full pl-10 pr-10 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-black focus:outline-none bg-white"
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

            <!-- Upload -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Surat Kuasa / Penunjukan
                </label>

                <label
                    class="flex flex-col items-center justify-center w-full px-6 py-8 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-black transition bg-gray-50"
                >
                    <div class="text-center space-y-2">
                        <ArrowUpTrayIcon class="w-8 h-8 text-gray-400 mx-auto" />
                        <p class="text-sm text-gray-600">
                            Klik untuk upload atau drag file ke sini
                        </p>
                        <p class="text-xs text-gray-400">
                            PDF, JPG, PNG (Max 2MB)
                        </p>
                        <p class="text-sm font-medium text-black">
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
                </div>

        </form>
        </div>
        <div class="pt-4 border-t bg-white shrink-0">

      <!-- Agreement -->
        <div class="flex items-start gap-2 text-sm mb-4">
            <input type="checkbox" v-model="form.agree" class="mt-1"/>
            <span>
            Saya menyetujui
            <Link href="/terms" class="text-black font-medium hover:underline">
                Syarat dan Ketentuan
            </Link>
            </span>
        </div>

        <!-- Submit -->
        <button
            type="button"
            @click="submit"
            :disabled="form.processing"
            class="w-full bg-red-700 text-white py-3 rounded-xl hover:bg-red-800 transition disabled:opacity-50"
        >
            {{ form.processing ? 'Memproses...' : 'Daftar Perusahaan' }}
        </button>

        </div>

    </div>

  </AuthLayout>
</template>
