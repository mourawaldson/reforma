</div>
<script>
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('/service-worker.js')
    .catch(function(e){ console.log('SW failed', e); });
}
</script>
</body>
</html>
