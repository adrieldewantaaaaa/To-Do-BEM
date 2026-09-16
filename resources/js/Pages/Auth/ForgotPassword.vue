<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import TdbButton from '@/Components/UI/TdbButton.vue';
import TdbInput from '@/Components/UI/TdbInput.vue';
defineProps({ status: String });
const form = useForm({ email: '' });
const submit = () => form.post(route('password.email'));
</script>
<template>
  <Head :title="'Reset password'" /><GuestLayout
    ><h1 class="tdb-heading text-3xl">{{ 'Reset your password.' }}</h1>
    <p class="mt-2 text-sm text-[var(--muted)]">{{ "We'll send a secure reset link to your email." }}</p>
    <p
      v-if="status"
      class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm font-semibold text-emerald-700"
    >
      {{ status }}
    </p>
    <form class="mt-7 space-y-5" @submit.prevent="submit">
      <TdbInput
        id="email"
        v-model="form.email"
        :label="'Email'"
        type="email"
        required
        autocomplete="email"
        placeholder="you@example.com"
        :error="form.errors.email"
      /><TdbButton class="w-full" type="submit" :disabled="form.processing">{{
        form.processing ? 'Sending…' : 'Email reset link'
      }}</TdbButton>
    </form>
    <p class="mt-6 text-center">
      <Link :href="route('login')" class="text-sm font-bold text-[var(--primary)] hover:underline">{{ 'Back to login' }}</Link>
    </p></GuestLayout
  >
</template>
