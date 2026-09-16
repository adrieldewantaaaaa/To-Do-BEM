<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import TdbButton from '@/Components/UI/TdbButton.vue';
import TdbInput from '@/Components/UI/TdbInput.vue';
const props = defineProps({ email: String, token: String });
const form = useForm({ token: props.token, email: props.email, password: '', password_confirmation: '' });
const submit = () =>
  form.post(route('password.store'), { onFinish: () => form.reset('password', 'password_confirmation') });
</script>
<template>
  <Head title="Choose new password" /><GuestLayout
    ><h1 class="tdb-heading text-3xl">Choose a new password.</h1>
    <form class="mt-7 space-y-5" @submit.prevent="submit">
      <TdbInput
        id="email"
        v-model="form.email"
        label="Email"
        type="email"
        required
        autocomplete="email"
        :error="form.errors.email"
      /><TdbInput
        id="password"
        v-model="form.password"
        label="New password"
        type="password"
        required
        autocomplete="new-password"
        :error="form.errors.password"
      /><TdbInput
        id="password-confirmation"
        v-model="form.password_confirmation"
        label="Confirm password"
        type="password"
        required
        autocomplete="new-password"
        :error="form.errors.password_confirmation"
      /><TdbButton class="w-full" type="submit" :disabled="form.processing">{{
        form.processing ? 'Saving…' : 'Reset password'
      }}</TdbButton>
    </form></GuestLayout
  >
</template>
