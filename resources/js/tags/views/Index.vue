<template>
  <div class="col-sm-12">
    <section class="content-header" style="margin-bottom: 25px;">
      <div class="row">
        <h1 class="col-md-11">Tags</h1>
        <button class="col-md-1 col-sm-2 btn btn-primary" @click="goToCreateTag()">Add New</button>
      </div>
    </section>

    <data-viewer
      source="tag"
      column="name"
      :hasAction="true"
      :canEdit="false"
      :canDelete="true"
      deleteSource="tag"
    />
  </div>
</template>

<script>
export default {
  data() {
    return {};
  },
  methods: {
    goToCreateTag() {
      this.$router.push({ path: "tag/create" });
    },
  },
};
</script>

<style>
</style>
