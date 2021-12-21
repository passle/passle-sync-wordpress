import "./App.css";

function App({ settings }) {
  const handleClick = async () => {
    const response = await fetch(
      "http://wordpressdemo.test/wp-json/wp/v2/posts"
    );
    const result = await response.text();
    console.log(result);
  };

  return (
    <div className="App">
      <h1>Hello, {settings?.name || "world"}!</h1>

      <button id="call-service" onClick={() => handleClick()}>
        Call service
      </button>
    </div>
  );
}

export default App;
