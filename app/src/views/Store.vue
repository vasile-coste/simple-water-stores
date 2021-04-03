<template>
  <div class="home">
    <NavBar location="" />

    <!-- rest of the page here -->
    <div class="headerMenu">
      <div class="container">
        <h2 class="headerMenu-title">Store {{ store.name }}</h2>
      </div>
    </div>

    <!-- Content -->
    <div class="container">
      <button type="button" @click="showModal(false, null)" class="btn btn-light">
        New Water
      </button>
      <div class="row mt-2">
        <div class="col-sm-12 col-md-8">
          <table class="table table-hover">
            <thead class="thead-dark">
              <tr>
                <th scope="col">#</th>
                <th scope="col">Water</th>
                <th scope="col">Stock</th>
                <th scope="col"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, indexArr) in waters" :key="indexArr">
                <th scope="row">{{ indexArr + 1 }}</th>
                <td>{{ item.name }}</td>
                <td>{{ item.stock }}</td>
                <td>
                  <i
                    class="btn btn-outline-info bi bi-pencil-square"
                    @click="showModal(item, indexArr)"
                  ></i>
                  <i
                    @click="deleteWater(item, indexArr)"
                    class="btn btn-outline-danger bi bi-x-circle"
                  ></i>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="col-sm-12 col-md-4 text-center">
          <canvas id="myChart" width="400" height="400"></canvas>
        </div>
      </div>
    </div>

    <!-- MODAL -->
    <div
      class="modal fade"
      id="formModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="formModal"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ modalTitle }}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="container-fluid">
              <div class="form-group">
                <label for="store" class="col-form-label">Name:</label>
                <input
                  type="text"
                  class="form-control"
                  id="store"
                  v-model="selectedWater.name"
                />
              </div>
              <div class="form-group">
                <label for="stock" class="col-form-label">Stock:</label>
                <input
                  type="text"
                  class="form-control"
                  id="stock"
                  v-model="selectedWater.stock"
                />
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
              Close
            </button>
            <button type="button" class="btn btn-primary" @click="saveWater">Save</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import NavBar from "@/components/NavBar.vue";
import $ from "jquery";
import Chart from "chart.js";
import helper from "@/js/helper";
export default {
  name: "Home",
  components: {
    NavBar,
  },
  data() {
    return {
      selectedWater: {
        name: null,
        store_id: null,
        stock: null,
      },
      modalTitle: "",
      indexArr: null,
      waters: [],
      store: {},
      chart: null,
    };
  },
  methods: {
    showModal(obj, index) {
      this.indexArr = index;
      if (index === null) {
        this.modalTitle = "New Water";
        this.selectedWater = {
          name: null,
          store_id: this.store.id,
          stock: null,
        };
      } else {
        this.modalTitle = "Update Water";
        this.selectedWater = {
          id: obj.id,
          name: obj.name,
          store_id: obj.store_id,
          stock: obj.stock,
        };
      }
      $("#formModal").modal("show");
    },
    deleteWater(obj, index) {
      if (!confirm("Are you sure?")) {
        return;
      }

      helper.toggleLoadingScreen(true);
      this.$axios
        .post(`water/delete`, obj)
        .then((res) => {
          let result = JSON.parse(res.request.response);
          if (result.success) {
            helper.showSuccess(result.message);
            this.waters.splice(index, 1);

            // reload chart
            this.loadChart();
          } else {
            helper.showWarning(result.message);
          }
          helper.toggleLoadingScreen(false);
        })
        .catch(() => {
          helper.toggleLoadingScreen(false);
        });
    },
    saveWater() {
      let err = false;
      if (!this.selectedWater.name || this.selectedWater.name.length == 0) {
        helper.showWarning("Please complete Water Name.");
        err = true;
      }
      if (!this.selectedWater.stock || this.selectedWater.stock.length == 0) {
        helper.showWarning("Please complete Water Stock.");
        err = true;
      }

      if (err) {
        return;
      }

      let urlPart = this.indexArr == null ? "add" : "update";

      helper.toggleLoadingScreen(true);
      this.$axios
        .post(`water/${urlPart}`, this.selectedWater)
        .then((res) => {
          let result = JSON.parse(res.request.response);
          if (result.success) {
            helper.showSuccess(result.message);
            if (this.indexArr == null) {
              /** add the new obj */
              this.waters.push(result.data);
            } else {
              /** update the obj */
              this.waters[this.indexArr].name = this.selectedWater.name;
              this.waters[this.indexArr].stock = this.selectedWater.stock;
            }

            // reload chart
            this.loadChart();
            $("#formModal").modal("hide");
          } else {
            helper.showWarning(result.message);
          }
          helper.toggleLoadingScreen(false);
        })
        .catch(() => {
          helper.toggleLoadingScreen(false);
        });
    },
    loadStore() {
      let storeId = this.$route.params.id;
      helper.toggleLoadingScreen(true);
      this.$axios
        .get(`store/find/${storeId}`)
        .then((res) => {
          let result = JSON.parse(res.request.response);
          if (result.success) {
            if (!result.data) {
              this.$router.push("/404");
            }
            this.store = result.data;
            this.loadWater(this.store.id);
          } else {
            helper.showWarning(result.message);
          }
          helper.toggleLoadingScreen(false);
        })
        .catch(() => {
          helper.toggleLoadingScreen(false);
        });
    },
    loadWater(storeId) {
      helper.toggleLoadingScreen(true);
      this.$axios
        .get(`water/${storeId}/all`)
        .then((res) => {
          let result = JSON.parse(res.request.response);
          if (result.success) {
            this.waters = result.data;

            // load chart
            this.loadChart();
          } else {
            helper.showWarning(result.message);
          }
          helper.toggleLoadingScreen(false);
        })
        .catch(() => {
          helper.toggleLoadingScreen(false);
        });
    },
    loadChart() {
      if (this.chart) {
        this.chart.destroy();
      }

      this.chart = new Chart(document.getElementById("myChart").getContext("2d"), {
        type: "bar",
        options: {
          title: {
            display: true,
            text: "Statistics",
          },
          scales: {
            yAxes: [
              {
                ticks: {
                  beginAtZero: true,
                },
              },
            ],
          },
        },
        data: {
          labels: this.waters.map((water) => water.name),
          datasets: [
            {
              label: "My First Dataset",
              data: this.waters.map((water) => parseInt(water.stock)),
              backgroundColor: [
                "#2ecc71",
                "#3498db",
                "#95a5a6",
                "#9b59b6",
                "#f1c40f",
                "#e74c3c",
                "#34495e",
              ],
            },
          ],
        },
      });
    },
  },
  mounted() {
    this.loadStore();
  },
};
</script>
