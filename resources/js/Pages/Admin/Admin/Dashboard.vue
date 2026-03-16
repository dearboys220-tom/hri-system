<script setup>
import { usePage } from '@inertiajs/vue3'
const { props } = usePage()
</script>

<template>
    <div style="padding: 40px;">
        <h1>Dashboard Admin</h1>
        <p>Selamat datang, {{ $page.props.auth.user.name }}! 🔑</p>

        <div style="margin-top: 30px; display: flex; gap: 20px;">
            <div style="background: #fee2e2; padding: 20px; border-radius: 8px; min-width: 160px;">
                <div style="font-size: 14px; color: #666;">Menunggu Persetujuan</div>
                <div style="font-size: 32px; font-weight: bold;">0</div>
            </div>
            <div style="background: #fef9c3; padding: 20px; border-radius: 8px; min-width: 160px;">
                <div style="font-size: 14px; color: #666;">Sedang Investigasi</div>
                <div style="font-size: 32px; font-weight: bold;">0</div>
            </div>
            <div style="background: #dcfce7; padding: 20px; border-radius: 8px; min-width: 160px;">
                <div style="font-size: 14px; color: #666;">Terverifikasi</div>
                <div style="font-size: 32px; font-weight: bold;">0</div>
            </div>
        </div>
    </div>
</template>