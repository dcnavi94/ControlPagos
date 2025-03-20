 <!-- chat_widget.php -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- Ícono de Chat -->
<div id="chatIcon">
  <i class="fas fa-comments"></i>
</div>

<!-- Ventana de Chat -->
<div id="chatWindow">
  <header>
    <span>Asistente Escolar</span>
    <span class="closeBtn">&times;</span>
  </header>
  <textarea id="userInput" rows="3" placeholder="Escribe tu pregunta..."></textarea>
  <button id="sendBtn">Enviar</button>
  <div id="response"></div>
</div>

<script>
  // Mostrar y ocultar la ventana de chat
  const chatIcon = document.getElementById('chatIcon');
  const chatWindow = document.getElementById('chatWindow');
  const closeBtn = document.querySelector('.closeBtn');

  chatIcon.addEventListener('click', () => {
    chatWindow.style.display = 'flex';
    chatIcon.style.display = 'none';
  });

  closeBtn.addEventListener('click', () => {
    chatWindow.style.display = 'none';
    chatIcon.style.display = 'flex';
  });

  // Manejar el envío de mensajes
  document.getElementById('sendBtn').addEventListener('click', function(){
    let userInput = document.getElementById('userInput').value;
    if(userInput.trim() === "") {
      return;
    }
    
    // Limpiar el campo de entrada después de enviar el mensaje
    document.getElementById('userInput').value = '';

    // Mostrar mensaje de carga
    document.getElementById('response').innerHTML = '<p>Cargando...</p>';

    fetch('chatgpt.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ prompt: userInput })
    })
    .then(response => response.json())
    .then(data => {
      if(data.error) {
        document.getElementById('response').innerHTML = 'Error: ' + data.error;
      } else {
        // Se asume que la respuesta se encuentra en data.choices[0].message.content
        let answer = data.choices[0].message.content;
        document.getElementById('response').innerHTML = '<p>' + answer + '</p>';
      }
    })
    .catch(error => {
      console.error('Error:', error);
      document.getElementById('response').innerHTML = 'Error en la conexión.';
    });
  });
</script>

<style>
  /* Ícono de chat */
  #chatIcon {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: #007bff;
    color: #fff;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 30px;
    z-index: 1000;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
  }
  /* Ventana de chat con bordes redondeados */
  #chatWindow {
    position: fixed;
    bottom: 90px;
    right: 20px;
    width: 300px;
    max-height: 400px;
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 10px; /* Esquinas menos cuadradas */
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    padding: 10px;
    display: none;
    flex-direction: column;
    z-index: 1000;
  }
  #chatWindow header {
    font-weight: bold;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  #chatWindow .closeBtn {
    cursor: pointer;
    font-size: 16px;
  }
  #chatWindow textarea {
    width: 100%;
    resize: none;
    border: 1px solid #ccc;
    border-radius: 5px; /* Bordes suaves para el textarea */
    padding: 5px;
  }
  #chatWindow #response {
    margin-top: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 5px;
    height: 150px;
    overflow-y: auto;
  }
  #chatWindow button {
    margin-top: 5px;
    width: 100%;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 5px; /* Bordes suaves para el botón */
    padding: 8px;
    cursor: pointer;
  }
  #chatWindow button:hover {
    background-color: #0056b3;
  }
</style>