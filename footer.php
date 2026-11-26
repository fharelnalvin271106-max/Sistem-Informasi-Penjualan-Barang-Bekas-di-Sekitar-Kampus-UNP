</main>
</div>
<script>
// Konfirmasi sebelum form dengan atribut data-confirm dikirim
document.addEventListener('submit', function (ev) {
  var msg = ev.target.getAttribute('data-confirm');
  if (msg && !window.confirm(msg)) ev.preventDefault();
});
// Pratinjau foto di form produk
document.addEventListener('change', function (ev) {
  var inp = ev.target;
  if (inp.name !== 'foto' || !inp.files || !inp.files[0]) return;
  var box = document.getElementById('foto-preview');
  if (!box) return;
  box.src = URL.createObjectURL(inp.files[0]);
  box.style.display = 'block';
});
</script>
</body>
</html>
