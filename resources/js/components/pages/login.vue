<template>
    <div class="contain">
        <div class="card">
            <div class="card-body">
                <div style="display: flex; flex-direction: column; align-items: center; gap: 15px;">
                    <RouterLink to="/" class="mb-4" style="width: 100%; height: 40px; display: flex; justify-content: center; align-items: center;">
                        <img src="/assets/images/logo-dark.png" alt="" style="max-width: 100%; max-height: 100%; width: 100%; height: 100%; object-fit: contain;">
                    </RouterLink>
                    <h4 class="mb-12">Sign In to your Account</h4>
                    <p class="mb-32 text-secondary-light text-lg">Welcome back! please enter your detail</p>
                    <form style="width: 100%;">
                        <div class="mb-3">
                            <label for="exampleInputEmail1" class="text-default-800 text-sm font-medium inline-block mb-2">Email address</label>
                            <input type="email" class="form-input" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter email">
                        </div>
                        <div class="mb-4">
                            <label for="exampleInputPassword1" class="text-default-800 text-sm font-medium inline-block mb-2">Password</label>
                            <input type="password" class="form-input" id="exampleInputPassword1" placeholder="Password">
                        </div>
                        <div class="">
                            <div class="d-flex justify-content-between gap-2" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                                <div class="form-check style-check d-flex align-items-center">
                                    <!-- <input class="form-check-input border border-neutral-300" type="checkbox" value="" id="remeber">
                                    <label class="form-check-label" for="remeber">Remember me </label> -->
                                </div>
                                <router-link to="/admins/forgotPassword" class="text-primary-600 fw-medium">Forgot Password?</router-link>
                            </div>
                        </div>
                        <button type="submit" class="btn bg-primary text-white">Submit</button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</template>
<script setup>

</script>
<style scoped>
    .contain{
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
</style>