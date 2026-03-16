<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
    email: '',
    password: '',
})

const submit = () => {
    form.post(route('staff.login.store'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #1e293b;">
        <div style="background: white; padding: 40px; border-radius: 12px; width: 100%; max-width: 400px; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">

            <!-- ロゴ・タイトル -->
            <div style="text-align: center; margin-bottom: 32px;">
                <div style="font-size: 24px; font-weight: bold; color: #1e293b;">HRI Staff Portal</div>
                <div style="font-size: 13px; color: #64748b; margin-top: 4px;">Admin / Investigator / Reviewer</div>
            </div>

            <!-- エラーメッセージ -->
            <div v-if="form.errors.email"
                style="background: #fee2e2; color: #dc2626; padding: 12px; border-radius: 6px; margin-bottom: 16px; font-size: 13px;">
                {{ form.errors.email }}
            </div>

            <!-- フォーム -->
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; color: #374151; margin-bottom: 6px;">Email</label>
                <input
                    v-model="form.email"
                    type="email"
                    placeholder="staff@hri-check.com"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; box-sizing: border-box;"
                />
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 13px; color: #374151; margin-bottom: 6px;">Password</label>
                <input
                    v-model="form.password"
                    type="password"
                    placeholder="••••••••"
                    style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; box-sizing: border-box;"
                    @keyup.enter="submit"
                />
            </div>

            <button
                @click="submit"
                :disabled="form.processing"
                style="width: 100%; padding: 12px; background: #1e293b; color: white; border: none; border-radius: 6px; font-size: 15px; font-weight: bold; cursor: pointer;">
                {{ form.processing ? 'Memproses...' : 'Masuk' }}
            </button>

            <div style="text-align: center; margin-top: 20px;">
                <a href="/login" style="font-size: 13px; color: #64748b;">← Kembali ke halaman utama</a>
            </div>

        </div>
    </div>
</template>