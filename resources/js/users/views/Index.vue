<template>
  <div class="col-sm-12">
    <section class="content-header" style="margin-bottom: 25px;">
      <div class="row">
        <h1 class="col-md-11">Users</h1>
        <button class="col-md-1 col-sm-2 btn btn-primary" @click="goToCreateUser()">Add New</button>
      </div>
    </section>

    <data-viewer
      source="users/all"
      column="username"
      :showDetail="true"
      detailPath="UserDetail"
      :hasAction="true"
      :canEdit="$root.$data.authUser.role === '1' ? true : false"
      :canDelete="$root.$data.authUser.role === '1' ? true : false"
      :editPath="$root.$data.authUser.role === '1' && 'UserEdit'"
      :deleteSource="$root.$data.authUser.role !== '3' ? 'user' : ''"
    />
  </div>
</template>

<script>
export default {
  data() {
    return {};
  },
  methods: {
    goToCreateUser() {
      this.$router.push({ path: "user/create" });
    },
  },
};
</script>

<style>
</style>
