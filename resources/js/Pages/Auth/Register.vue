<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import TdbButton from '@/Components/UI/TdbButton.vue';
import TdbInput from '@/Components/UI/TdbInput.vue';
const form = useForm({ name: '', email: '', password: '', password_confirmation: '' });
const submit = () => form.post(route('register'), { onFinish: () => form.reset('password', 'password_confirmation') });
</script>
<template>
  <Head title="Create account" /><GuestLayout
    ><h1 class="tdb-heading text-3xl">Make room for good work.</h1>
    <p class="mt-2 text-sm text-[var(--muted)]">Create your personal workspace in a minute.</p>
    <form class="mt-7 space-y-4" @submit.prevent="submit">
      <TdbInput
        id="name"
        v-model="form.name"
        label="Name"
        required
        maxlength="255"
        autocomplete="name"
        placeholder="Your name"
        :error="form.errors.name"
      /><TdbInput
        id="email"
        v-model="form.email"
        label="Email"
        type="email"
        required
        autocomplete="email"
        placeholder="you@example.com"
        :error="form.errors.email"
      /><TdbInput
        id="password"
        v-model="form.password"
        label="Password"
        type="password"
        required
        autocomplete="new-password"
        placeholder="At least 8 characters"
        :error="form.errors.password"
      /><TdbInput
        id="password-confirmation"
        v-model="form.password_confirmation"
        label="Confirm password"
        type="password"
        required
        autocomplete="new-password"
        placeholder="Repeat your password"
        :error="form.errors.password_confirmation"
      /><TdbButton class="w-full" type="submit" :disabled="form.processing">{{
        form.processing ? 'Creating…' : 'Create account'
      }}</TdbButton>
    </form>
    <p class="mt-6 text-center text-sm text-[var(--muted)]">
      Already have an account?
      <Link :href="route('login')" class="font-bold text-[var(--primary)] hover:underline">Log in</Link>
    </p></GuestLayout
  >
</template>
